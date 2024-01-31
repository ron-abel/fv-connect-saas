<?php

namespace App\Services;


use Illuminate\Support\Facades\Log as Logging;

use Exception;
use Illuminate\Support\Facades\DB;

use Stichoza\GoogleTranslate\GoogleTranslate;

use App\Services\SendGridServices;
use App\Services\NotificationHandlerService;
use App\Services\VariableService;
use App\Jobs\HandleKillTask;
use App\Services\SMSTimezoneHandleService;

use App\Models\Blacklist;
use App\Models\Tenant;
use App\Models\FeedbackNoteManager;
use App\Models\AutomatedWorkflowLog;
use App\Models\AutomatedWorkflowActionLog;
use App\Models\TenantNotificationConfig;
use App\Models\LanguageLog;

class TriggerActionService
{

    public static function callActionService($action, $log_id)
    {
        try {
            if ($action->status == 'pause') {
                $log_data = [
                    'tenant_id' => $action->tenant_id,
                    'automated_workflow_log_id' => $log_id,
                    'trigger_id' => $action->trigger_id,
                    'action_id' => $action->id,
                    'note_body' => 'The Action was paused.'
                ];
                AutomatedWorkflowActionLog::create($log_data);
                return;
            }
            $log = AutomatedWorkflowLog::where('id', $log_id)->first();
            $log->is_handled = 1;
            $log->save();
            if ($action->action_short_code == '1') {
                self::sendSMStoClient($action, $log);
            } else if ($action->action_short_code == '2') {
                self::setClientBlacklist($action, $log);
            } else if ($action->action_short_code == '3') {
                self::createNote($action, $log);
            } else if ($action->action_short_code == '4') {
                self::createTask($action, $log);
            } else if ($action->action_short_code == '5') {
                self::sendAdminNotificationEmail($action, $log);
            } else if ($action->action_short_code == '6') {
                self::addProjectHashtag($action, $log);
            } else if ($action->action_short_code == '7') {
                self::addClientHashtag($action, $log);
            } else if ($action->action_short_code == '8') {
                self::toggleSectionVisibility($action, $log);
            } else if ($action->action_short_code == '9') {
                self::killAllTasks($action, $log);
            } else if ($action->action_short_code == '10') {
                self::changeProjectPhase($action, $log);
            } else if ($action->action_short_code == '11') {
                self::sendToWebhook($action, $log);
            } else if ($action->action_short_code == '12') {
                self::emailClient($action, $log);
            } else if ($action->action_short_code == '13') {
                self::mirrorAField($action, $log);
            } else if ($action->action_short_code == '14') {
                self::updateProjectTeam($action, $log);
            }
        } catch (Exception $e) {
            Logging::warning($e->getMessage());
        }
    }

    /**
     * Send SMS to client
     */
    public static function sendSMStoClient($action, $log)
    {
        try {
            $tenant_id = $action->tenant_id;
            $sent_phone_numbers = [];
            $project_id = 0;
            $message_body = $action->client_sms_body;

            $tenant_details = Tenant::where('id', $tenant_id)->first();
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
                $apiurl = $tenant_details->fv_api_base_url;
            }
            $filevine_api = new FilevineService($apiurl, "", $tenant_id);

            $config = DB::table('config')->where('tenant_id', $tenant_id)->first();

            // Except Request Object Project,OrgMember,Sms we get ProjectId on Request Object
            $request_json = json_decode($log->webhook_request_json);
            if (isset($request_json->Object) && $request_json->Object == 'Contact') {
                $client_id = $request_json->ObjectId->PersonId;
            } else {
                $project_id = isset($request_json->ProjectId) ? $request_json->ProjectId : (isset($request_json->ObjectId->ProjectId) ? $request_json->ObjectId->ProjectId : null);
                $project_details = json_decode($filevine_api->getProjectsById($project_id));
                $client_id = $project_details->clientId->native;
            }

            if ($action->send_sms_choice == 'To Person Field' && !empty($project_id)) {
                $section_details = json_decode($filevine_api->getProjectFormsTeamInfo([
                    'projectId' => $project_id,
                    'section' => $action->person_field_project_type_section_selector,
                    'fields' => $action->person_field_project_type_section_field_selector
                ]));

                if (!empty($section_details)) {
                    $field_selector = $action->person_field_project_type_section_field_selector;
                    $section_details = $section_details->$field_selector;
                    $contact_phones = isset($section_details->phones) ? $section_details->phones : [];
                    $contact_phones = array_reverse($contact_phones);
                    foreach ($contact_phones as $phone) {
                        $to_number = isset($phone->rawNumber) ? $phone->rawNumber : null;
                        if (!empty($to_number)) {
                            $sent_phone_numbers[] = $to_number;
                        }
                    }
                }
            }

            if (count($sent_phone_numbers) <= 0) {
                $contact_details = json_decode($filevine_api->getContactByContactId($client_id));
                if (!empty($contact_details) && isset($contact_details->phones) && count($contact_details->phones)) {
                    $contact_phones = $contact_details->phones;
                    $contact_phones = array_reverse($contact_phones);
                    foreach ($contact_phones as $phone) {
                        $to_number = isset($phone->rawNumber) ? $phone->rawNumber : null;
                        if ($to_number == null || in_array($to_number, $sent_phone_numbers)) {
                            continue;
                        }
                        if ($config->default_sms_way_status) {
                            if ($config->default_sms_way == 'labeled_number') {
                                if (!isset($phone->label) || empty($phone->label)) {
                                    continue;
                                }
                            } else  if ($config->default_sms_way == 'broadcast_number') {
                                $default_sms_custom_contact_labels = explode(',', $config->default_sms_custom_contact_label);
                                if (!isset($phone->label) || empty($phone->label) || !in_array($phone->label, $default_sms_custom_contact_labels)) {
                                    continue;
                                }
                            } else {
                                if (count($sent_phone_numbers) == 1) {
                                    continue;
                                }
                            }
                        }
                        $sent_phone_numbers[] = $to_number;
                    }
                }
            }

            if (count($sent_phone_numbers)) {

                $additional_data = [
                    'fv_project_id' => $project_id,
                    'fv_client_id' => $client_id,
                    'tenant_id' => $tenant_id,
                ];

                $message_body = self::updateMessageBody($message_body, $additional_data);

                // Find client language and translate
                $language = LanguageLog::where('tenant_id', $tenant_id)->where('fv_client_id', $client_id)->orderBy('updated_at', 'DESC')->first();
                $latest_language = 'en';
                if ($language != null) {
                    $latest_language = $language->language;
                }
                $tr = new GoogleTranslate();
                $tr->setSource();
                $tr->setTarget($latest_language);
                $message_body = $tr->translate($message_body);

                if (env('APP_ENV') == 'production') {
                    $sms_number_from = env('TWILIO_FROM');
                    foreach ($sent_phone_numbers as $to_number) {
                        $log_data = [
                            'tenant_id' => $tenant_id,
                            'automated_workflow_log_id' => $log->id,
                            'trigger_id' => $action->trigger_id,
                            'action_id' => $action->id,
                            'fv_project_id' => $project_id,
                            'fv_client_id' => $client_id,
                            'sms_phones' => $to_number,
                            'note_body' => $message_body
                        ];

                        if ($action->status == 'live') {
                            $smsTimezoneService = new SMSTimezoneHandleService();
                            $smsTimezoneService->createSMSJobByTimezone($to_number, $sms_number_from, $message_body, $tenant_id);
                            $log_data['is_handled'] = true;
                        }

                        AutomatedWorkflowActionLog::create($log_data);
                        self::callNotificationHandlerService($log_data);
                    }
                }
            } else {
                $log_data = [
                    'tenant_id' => $tenant_id,
                    'automated_workflow_log_id' => $log->id,
                    'trigger_id' => $action->trigger_id,
                    'action_id' => $action->id,
                    'fv_project_id' => $project_id,
                    'fv_client_id' => $client_id,
                    'sms_phones' => '',
                    'note_body' => 'There is not any matched phone number to send SMS.'
                ];
                AutomatedWorkflowActionLog::create($log_data);
            }
        } catch (Exception $e) {
            Logging::warning($e->getMessage());
        }
    }

    /**
     * Set Client into blacklist
     */
    public static function setClientBlacklist($action, $log)
    {
        try {
            $tenant_id = $action->tenant_id;
            $project_id = 0;

            $tenant_details = Tenant::where('id', $tenant_id)->first();
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
                $apiurl = $tenant_details->fv_api_base_url;
            }
            $filevine_api = new FilevineService($apiurl, "", $tenant_id);

            $request_json = json_decode($log->webhook_request_json);
            if (isset($request_json->Object) && $request_json->Object == 'Contact') {
                $client_id = $request_json->ObjectId->PersonId;
                $contact_details = json_decode($filevine_api->getContactByContactId($client_id));
                $full_name = $contact_details->fullName;
            } else {
                $project_id = isset($request_json->ProjectId) ? $request_json->ProjectId : (isset($request_json->ObjectId->ProjectId) ? $request_json->ObjectId->ProjectId : null);
                $project_details = json_decode($filevine_api->getProjectsById($project_id));
                $client_id = $project_details->clientId->native;
                $full_name = $project_details->clientName;
            }

            $log_data = [
                'tenant_id' => $tenant_id,
                'automated_workflow_log_id' => $log->id,
                'trigger_id' => $action->trigger_id,
                'action_id' => $action->id,
                'fv_project_id' => $project_id,
                'fv_client_id' => $client_id
            ];

            if ($action->status == 'live') {
                $is_exist = Blacklist::where('tenant_id', $tenant_id)->where('fv_client_id', $client_id)->exists();
                if (!$is_exist) {
                    Blacklist::create([
                        'tenant_id' => $tenant_id,
                        'fv_full_name' => $full_name,
                        'fv_client_id' => $client_id
                    ]);
                }
                $log_data['is_handled'] = true;
            }
            AutomatedWorkflowActionLog::create($log_data);
        } catch (Exception $e) {
            Logging::warning($e->getMessage());
        }
    }

    /**
     * Create note on filevine
     */
    public static function createNote($action, $log)
    {
        try {
            $note_body = $action->fv_project_note_body;
            $is_pinned = $action->fv_project_note_with_pin;
            $tenant_id = $action->tenant_id;

            $tenant_details = Tenant::where('id', $tenant_id)->first();
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
                $apiurl = $tenant_details->fv_api_base_url;
            }
            $filevine_api = new FilevineService($apiurl, "", $tenant_id);

            $request_json = json_decode($log->webhook_request_json);
            if (isset($request_json->Object) && $request_json->Object == 'Contact') {
                Logging::warning("This request object " . $request_json->Object . " is not eligible for note creation action!");
                return false;
            }
            $project_id = isset($request_json->ProjectId) ? $request_json->ProjectId : (isset($request_json->ObjectId->ProjectId) ? $request_json->ObjectId->ProjectId : null);
            $project_details = json_decode($filevine_api->getProjectsById($project_id));
            $client_id = $project_details->clientId->native;

            $additional_data = [
                'fv_project_id' => $project_id,
                'fv_client_id' => $client_id,
                'tenant_id' => $tenant_id,
            ];
            $note_body = self::updateMessageBody($note_body, $additional_data);

            $params = [
                'projectId' => ['native' => $project_id, 'partner' => null],
                'body' => $note_body,
                'authorId' => ['native' => $client_id, 'partner' => null],
                'isPinnedToProject' => $is_pinned ? true : false
            ];

            $log_data = [
                'tenant_id' => $tenant_id,
                'automated_workflow_log_id' => $log->id,
                'trigger_id' => $action->trigger_id,
                'action_id' => $action->id,
                'fv_project_id' => $project_id,
                'fv_client_id' => $client_id,
                'note_body' => $note_body
            ];
            if ($action->status == 'live') {
                $filevine_api->createNote($params);
                $log_data['is_handled'] = true;
            }
            AutomatedWorkflowActionLog::create($log_data);
            self::callNotificationHandlerService($log_data);
        } catch (Exception $e) {
            Logging::warning($e->getMessage());
        }
    }

    /**
     * Create note/task with assign user on filevine
     */
    public static function createTask($action, $log)
    {
        try {
            $note_body = $action->fv_project_task_body;
            $tenant_id = $action->tenant_id;
            $assigneeId = $action->fv_project_task_assign_user_id;
            $fv_project_task_assign_type = $action->fv_project_task_assign_type;
            $fv_project_task_assign_user_role_name = $action->fv_project_task_assign_user_role_name;

            $tenant_details = Tenant::where('id', $tenant_id)->first();
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
                $apiurl = $tenant_details->fv_api_base_url;
            }
            $filevine_api = new FilevineService($apiurl, "", $tenant_id);

            $request_json = json_decode($log->webhook_request_json);
            if (isset($request_json->Object) && $request_json->Object == 'Contact') {
                Logging::warning("This request object " . $request_json->Object . " is not eligible for task creation action!");
                return false;
            }
            $project_id = isset($request_json->ProjectId) ? $request_json->ProjectId : (isset($request_json->ObjectId->ProjectId) ? $request_json->ObjectId->ProjectId : null);
            $project_details = json_decode($filevine_api->getProjectsById($project_id));
            $client_id = $project_details->clientId->native;

            // Possibly need to modify this user role part
            if ($fv_project_task_assign_type == 'role') {
                $user_details = json_decode($filevine_api->getAllUsersList());
                if (isset($user_details->items) && !empty($user_details->items)) {
                    $item_details = $user_details->items;
                    foreach ($item_details as $item) {
                        $accessLevel = $item->permissions->accessLevel;
                        if (strtolower($accessLevel) == strtolower($fv_project_task_assign_user_role_name)) {
                            $assigneeId = $item->user->userId->native;
                            break;
                        }
                    }
                    if (empty($assigneeId)) {
                        foreach ($item_details as $item) {
                            $assigneeId = $item->user->userId->native;
                            break;
                        }
                    }
                }
            }

            $additional_data = [
                'fv_project_id' => $project_id,
                'fv_client_id' => $client_id,
                'tenant_id' => $tenant_id,
            ];
            $note_body = self::updateMessageBody($note_body, $additional_data);

            $params = [
                'projectId' => ['native' => $project_id, 'partner' => null],
                'body' => $note_body,
                'assigneeId' => ['native' => $assigneeId, 'partner' => null]
            ];

            $log_data = [
                'tenant_id' => $tenant_id,
                'automated_workflow_log_id' => $log->id,
                'trigger_id' => $action->trigger_id,
                'action_id' => $action->id,
                'fv_project_id' => $project_id,
                'fv_client_id' => $client_id,
                'note_body' => $note_body
            ];

            if ($action->status == 'live') {
                $filevine_api->createTask($params);
                $log_data['is_handled'] = true;
            }
            AutomatedWorkflowActionLog::create($log_data);
            self::callNotificationHandlerService($log_data);
            return true;
        } catch (Exception $e) {
            Logging::warning($e->getMessage());
        }
    }

    /**
     * Send a notification email to admin
     */
    public static function sendAdminNotificationEmail($action, $log)
    {
        try {
            $tenant_id = $action->tenant_id;
            $email_content = $action->email_note_body;
            $request_json = json_decode($log->webhook_request_json);
            $project_id = (isset($request_json->ProjectId) && !empty($request_json->ProjectId)) ? $request_json->ProjectId : 0;
            $tenant_details = Tenant::where('id', $tenant_id)->first();
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
                $apiurl = $tenant_details->fv_api_base_url;
            }
            $filevine_api = new FilevineService($apiurl, "", $tenant_id);
            $project_details = json_decode($filevine_api->getProjectsById($project_id));
            $client_id = isset($project_details->clientId->native) ? $project_details->clientId->native : 0;

            $send_grid_obj = new SendGridServices($tenant_id);
            $records = FeedbackNoteManager::where('tenant_id', $tenant_id)->get();

            $additional_data = [
                'fv_project_id' => $project_id,
                'fv_client_id' => $client_id,
                'tenant_id' => $tenant_id,
            ];
            $email_content = self::updateMessageBody($email_content, $additional_data);

            $sg_data = [
                'trigger_name' => $action->trigger_name,
                'action_name' => $action->action_name,
                'notification_body' => $email_content,
                'client_name' => isset($tenant_details->tenant_name) ? $tenant_details->tenant_name : '',
                'fv_project_id' => $project_id,
                'fv_project_name' => isset($project_details->projectName) ? $project_details->projectName : ''
            ];

            foreach ($records as $record) {

                $log_data = [
                    'tenant_id' => $tenant_id,
                    'automated_workflow_log_id' => $log->id,
                    'trigger_id' => $action->trigger_id,
                    'action_id' => $action->id,
                    'emails' => $record->email,
                    'note_body' => $email_content,
                    'fv_project_id' => $project_id,
                    'fv_client_id' => $client_id
                ];

                if ($action->status == 'live') {
                    $send_grid_obj->sendTriggerAdminNotificationEmail(trim($record->email), $sg_data);
                    $log_data['is_handled'] = true;
                }

                AutomatedWorkflowActionLog::create($log_data);
            }
            return true;
        } catch (Exception $e) {
            Logging::warning($e->getMessage());
        }
    }

    /**
     * Add custom hastag into FV project
     * Hash tag is not updated but project name updated, need to debug more
     */
    public static function addProjectHashtag($action, $log)
    {
        try {
            $tenant_id = $action->tenant_id;
            $hashtag = $action->fv_project_hashtag;

            $tenant_details = Tenant::where('id', $tenant_id)->first();
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
                $apiurl = $tenant_details->fv_api_base_url;
            }

            $filevine_api = new FilevineService($apiurl, "", $tenant_id);

            $request_json = json_decode($log->webhook_request_json);
            if (isset($request_json->Object) && $request_json->Object == 'Contact') {
                Logging::warning("This request object " . $request_json->Object . " is not eligible for add project hashtag action!");
                return false;
            }
            $project_id = isset($request_json->ProjectId) ? $request_json->ProjectId : (isset($request_json->ObjectId->ProjectId) ? $request_json->ObjectId->ProjectId : null);

            $param = [
                "projects" => [
                    [
                        "Native" => $project_id,
                        "Partner" => null
                    ]
                ]
            ];

            $log_data = [
                'tenant_id' => $tenant_id,
                'automated_workflow_log_id' => $log->id,
                'trigger_id' => $action->trigger_id,
                'action_id' => $action->id,
                'fv_project_id' => $project_id,
                'note_body' => $hashtag
            ];

            if ($action->status == 'live') {
                $filevine_api->addNewHashTag($hashtag, $param);
                $log_data['is_handled'] = true;
            }

            AutomatedWorkflowActionLog::create($log_data);
        } catch (Exception $e) {
            Logging::warning($e->getMessage());
        }
    }


    /**
     * Add custom hastag into FV project
     * Could not find any hastag item on client object details
     */
    public static function addClientHashtag($action, $log)
    {
        try {
            $tenant_id = $action->tenant_id;
            $hashtag = $action->fv_project_hashtag;
            $project_id = 0;

            $tenant_details = Tenant::where('id', $tenant_id)->first();
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
                $apiurl = $tenant_details->fv_api_base_url;
            }
            $filevine_api = new FilevineService($apiurl, "", $tenant_id);

            $request_json = json_decode($log->webhook_request_json);
            if (isset($request_json->Object) && $request_json->Object == 'Contact') {
                $client_id = $request_json->ObjectId->PersonId;
            } else {
                $project_id = isset($request_json->ProjectId) ? $request_json->ProjectId : (isset($request_json->ObjectId->ProjectId) ? $request_json->ObjectId->ProjectId : null);
                $project_details = json_decode($filevine_api->getProjectsById($project_id));
                $client_id = $project_details->clientId->native;
            }
            $client_details = json_decode($filevine_api->getContactByContactId($client_id));

            // Need to add hashtag add function

            $log_data = [
                'tenant_id' => $tenant_id,
                'automated_workflow_log_id' => $log->id,
                'trigger_id' => $action->trigger_id,
                'action_id' => $action->id,
                'fv_project_id' => $project_id,
                'fv_client_id' => $client_id,
                'note_body' => $hashtag
            ];
            AutomatedWorkflowActionLog::create($log_data);
            self::callNotificationHandlerService($log_data);
        } catch (Exception $e) {
            Logging::warning($e->getMessage());
        }
    }

    /**
     * Call Notification Handler Service
     */
    public static function callNotificationHandlerService($log_data)
    {
        try {
            $notification_config = TenantNotificationConfig::where('tenant_id', $log_data['tenant_id'])->where('event_short_code', TenantNotificationConfig::WorkflowExecuted)->first();
            if ($notification_config) {
                $params = [
                    'project_id' => $log_data['fv_project_id'],
                    'tenant_id' => $log_data['tenant_id'],
                    'client_id' => $log_data['fv_client_id'],
                    'note_body' => $log_data['note_body'],
                    'action_name' => 'Workflow Executed Successfully',
                ];
                NotificationHandlerService::callActionService($notification_config, $params);
            }
        } catch (Exception $e) {
            Logging::warning($e->getMessage());
        }
    }

    /**
     * Toggle Section Visibility trigger action service
     */
    public static function toggleSectionVisibility($action, $log)
    {
        try {
            $section_visibility_section_selector = $action->section_visibility_section_selector;
            $section_visibility = $action->section_visibility;
            $tenant_id = $action->tenant_id;

            $tenant_details = Tenant::where('id', $tenant_id)->first();
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
                $apiurl = $tenant_details->fv_api_base_url;
            }
            $filevine_api = new FilevineService($apiurl, "", $tenant_id);

            $request_json = json_decode($log->webhook_request_json);
            $project_id = isset($request_json->ProjectId) ? $request_json->ProjectId : (isset($request_json->ObjectId->ProjectId) ? $request_json->ObjectId->ProjectId : 0);
            $client_id = isset($request_json->fv_client_id) ? $request_json->fv_client_id : 0;
            if (!$client_id) {
                $project_details = json_decode($filevine_api->getProjectsById($project_id));
                $client_id = $project_details->clientId->native;
            }

            $params = [
                'sectionSelector' => $section_visibility_section_selector,
                'sectionVisibility' => $section_visibility
            ];

            $log_data = [
                'tenant_id' => $tenant_id,
                'automated_workflow_log_id' => $log->id,
                'trigger_id' => $action->trigger_id,
                'action_id' => $action->id,
                'fv_project_id' => $project_id,
                'fv_client_id' => $client_id,
                'note_body' => $section_visibility
            ];

            if ($action->status == 'live') {
                $filevine_api->toggleSectionVisibility($project_id, $params);
                $log_data['is_handled'] = true;
            }
            AutomatedWorkflowActionLog::create($log_data);
        } catch (Exception $e) {
            Logging::warning($e->getMessage());
        }
    }


    /**
     * Kill All Tasks trigger action service
     */
    public static function killAllTasks($action, $log)
    {
        try {

            $tenant_id = $action->tenant_id;

            $tenant_details = Tenant::where('id', $tenant_id)->first();
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
                $apiurl = $tenant_details->fv_api_base_url;
            }
            $filevine_api = new FilevineService($apiurl, "", $tenant_id);

            $request_json = json_decode($log->webhook_request_json);
            $project_id = isset($request_json->ProjectId) ? $request_json->ProjectId : (isset($request_json->ObjectId->ProjectId) ? $request_json->ObjectId->ProjectId : null);
            $client_id = isset($request_json->fv_client_id) ? $request_json->fv_client_id : 0;

            $log_data = [
                'tenant_id' => $tenant_id,
                'automated_workflow_log_id' => $log->id,
                'trigger_id' => $action->trigger_id,
                'action_id' => $action->id,
                'fv_project_id' => $project_id,
                'fv_client_id' => $client_id,
                'note_body' => $action->action_name
            ];

            if ($action->status == 'live') {
                $task_details = json_decode($filevine_api->getProjectTaskList($project_id));
                if (isset($task_details->items) && !empty($task_details->items)) {
                    $item_details = $task_details->items;
                    foreach ($item_details as $item) {
                        $noteId = $item->noteId->native;
                        $job_delay_time = now()->addSeconds(10);
                        $job = (new HandleKillTask($filevine_api, $noteId))->delay($job_delay_time);
                        $jId = app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatch($job);
                    }
                }
                $log_data['is_handled'] = true;
            }
            AutomatedWorkflowActionLog::create($log_data);
        } catch (Exception $e) {
            Logging::warning($e->getMessage());
        }
    }

    /**
     * Change Project Phase trigger action service
     */
    public static function changeProjectPhase($action, $log)
    {
        try {
            $phase_assignment = $action->phase_assignment;
            $project_phase_id_native = $action->project_phase_id_native;
            $tenant_id = $action->tenant_id;

            $tenant_details = Tenant::where('id', $tenant_id)->first();
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
                $apiurl = $tenant_details->fv_api_base_url;
            }
            $filevine_api = new FilevineService($apiurl, "", $tenant_id);

            $request_json = json_decode($log->webhook_request_json);
            $project_id = isset($request_json->ProjectId) ? $request_json->ProjectId : (isset($request_json->ObjectId->ProjectId) ? $request_json->ObjectId->ProjectId : null);

            if ($phase_assignment == 'Next_Sequential_Phase') {
                $project_details = json_decode($filevine_api->getProjectsById($project_id));
                $client_id = $project_details->clientId->native;
                $project_type_id = $project_details->projectTypeId->native;
                $phaseId = $project_details->phaseId->native;
                $fv_project_type_phase_list = json_decode($filevine_api->getProjectTypePhaseList($project_type_id), true);
                $phase_list = [];
                if (isset($fv_project_type_phase_list['items']) and !empty($fv_project_type_phase_list['items'])) {
                    $phases = collect($fv_project_type_phase_list['items']);
                    foreach ($phases as $key => $phase) {
                        $phase_list[] = $phase['phaseId']['native'];
                    }
                }
                if (count($phase_list) && in_array($phaseId, $phase_list)) {
                    $key = array_search($phaseId, $phase_list) + 1;
                    if (count($phase_list) == $key) {
                        $key = 0;
                    }
                    $project_phase_id_native = $phase_list[$key];
                }
            } else {
                $client_id = isset($request_json->fv_client_id) ? $request_json->fv_client_id : 0;
            }

            $params = [
                'phaseId' => ['native' => $project_phase_id_native],
            ];

            $log_data = [
                'tenant_id' => $tenant_id,
                'automated_workflow_log_id' => $log->id,
                'trigger_id' => $action->trigger_id,
                'action_id' => $action->id,
                'fv_project_id' => $project_id,
                'fv_client_id' => $client_id,
                'note_body' => $phase_assignment
            ];

            if ($action->status == 'live') {
                $filevine_api->updateProjectsDetailsById($project_id, $params);
                $log_data['is_handled'] = true;
            }
            AutomatedWorkflowActionLog::create($log_data);
        } catch (Exception $e) {
            Logging::warning($e->getMessage());
        }
    }

    /**
     * Send to Webhook trigger action service
     */
    public static function sendToWebhook($action, $log)
    {
        try {
            $delivery_hook_url = $action->delivery_hook_url;
            $tenant_id = $action->tenant_id;
            $client_id = 0;
            $project_details = [];
            $contact_details = [];

            $tenant_details = Tenant::where('id', $tenant_id)->first();
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
                $apiurl = $tenant_details->fv_api_base_url;
            }
            $filevine_api = new FilevineService($apiurl, "", $tenant_id);

            $request_json = json_decode($log->webhook_request_json);
            $project_id = isset($request_json->ProjectId) ? $request_json->ProjectId : (isset($request_json->ObjectId->ProjectId) ? $request_json->ObjectId->ProjectId : 0);
            if ($project_id) {
                $project_details = json_decode($filevine_api->getProjectsById($project_id));
                $client_id = isset($project_details->clientId->native) ? $project_details->clientId->native : 0;
            }

            if (!$client_id) {
                $client_id = isset($request_json->ObjectId->PersonId) ? $request_json->ObjectId->PersonId : 0;
            }

            if ($client_id) {
                $contact_details = json_decode($filevine_api->getContactByContactId($client_id));
            }

            $data_array = array('payload_data' => $request_json, "contact_data" => $contact_details, "project_data" => $project_details);

            $log_data = [
                'tenant_id' => $tenant_id,
                'automated_workflow_log_id' => $log->id,
                'trigger_id' => $action->trigger_id,
                'action_id' => $action->id,
                'fv_project_id' => $project_id,
                'fv_client_id' => $client_id,
                'note_body' => $action->action_name
            ];

            if ($action->status == 'live') {
                $ch = curl_init($delivery_hook_url);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_array));
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($ch);
                curl_close($ch);
                $log_data['is_handled'] = true;
            }
            AutomatedWorkflowActionLog::create($log_data);
        } catch (Exception $e) {
            Logging::warning($e->getMessage());
        }
    }


    /**
     * Send an Email Client
     */
    public static function emailClient($action, $log)
    {
        try {
            $tenant_id = $action->tenant_id;
            $email_content = $action->email_note_body;
            $request_json = json_decode($log->webhook_request_json);
            $project_id = (isset($request_json->ProjectId) && !empty($request_json->ProjectId)) ? $request_json->ProjectId : 0;
            $tenant_details = Tenant::where('id', $tenant_id)->first();
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
                $apiurl = $tenant_details->fv_api_base_url;
            }
            $filevine_api = new FilevineService($apiurl, "", $tenant_id);
            $project_details = json_decode($filevine_api->getProjectsById($project_id));
            $client_id = isset($project_details->clientId->native) ? $project_details->clientId->native : 0;

            $send_grid_obj = new SendGridServices($tenant_id);
            $contact_details = json_decode($filevine_api->getContactByContactId($client_id));
            $email_address = "";
            $client_name = "";
            if (!empty($contact_details) && isset($contact_details->primaryEmail)) {
                $email_address = $contact_details->primaryEmail;
                $client_name = isset($contact_details->fullName) ? $contact_details->fullName : "";
            }

            $additional_data = [
                'fv_project_id' => $project_id,
                'fv_client_id' => $client_id,
                'tenant_id' => $tenant_id,
            ];
            $email_content = self::updateMessageBody($email_content, $additional_data);

            $sg_data = [
                'trigger_name' => $action->trigger_name,
                'action_name' => $action->action_name,
                'notification_body' => $email_content,
                'client_name' => $client_name,
                'fv_project_id' => $project_id,
                'fv_project_name' => isset($project_details->projectName) ? $project_details->projectName : ''
            ];

            $log_data = [
                'tenant_id' => $tenant_id,
                'automated_workflow_log_id' => $log->id,
                'trigger_id' => $action->trigger_id,
                'action_id' => $action->id,
                'emails' => $email_address,
                'note_body' => $email_content,
                'fv_project_id' => $project_id,
                'fv_client_id' => $client_id
            ];

            if ($action->status == 'live') {
                $send_grid_obj->sendTriggerClientNotificationEmail(trim($email_address), $sg_data);
                $log_data['is_handled'] = true;
            }
            AutomatedWorkflowActionLog::create($log_data);

            return true;
        } catch (Exception $e) {
            Logging::warning($e->getMessage());
        }
    }

    /**
     * Mirror A Field
     */
    public static function mirrorAField($action, $log)
    {
        try {
            $tenant_id = $action->tenant_id;
            $request_json = json_decode($log->webhook_request_json);
            $project_id = (isset($request_json->ProjectId) && !empty($request_json->ProjectId)) ? $request_json->ProjectId : 0;
            $tenant_details = Tenant::where('id', $tenant_id)->first();
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
                $apiurl = $tenant_details->fv_api_base_url;
            }
            $filevine_api = new FilevineService($apiurl, "", $tenant_id);
            $project_details = json_decode($filevine_api->getProjectsById($project_id));
            $client_id = isset($project_details->clientId->native) ? $project_details->clientId->native : 0;

            $mirror_from_field_project_type_section_selector = $action->mirror_from_field_project_type_section_selector;
            $mirror_from_field_project_type_section_field_selector = $action->mirror_from_field_project_type_section_field_selector;
            $mirror_from_field_project_type_section_field_selector = $action->mirror_from_field_project_type_section_field_selector;
            $mirror_from_field_project_type_section_field_selector_type = $action->mirror_from_field_project_type_section_field_selector_type;
            $mirror_to_field_project_type_section_selector = $action->mirror_to_field_project_type_section_selector;
            $mirror_to_field_project_type_section_field_selector = $action->mirror_to_field_project_type_section_field_selector;
            $mirror_to_field_project_type_section_field_selector_type = $action->mirror_to_field_project_type_section_field_selector_type;
            $section_details = json_decode($filevine_api->getProjectFormsTeamInfo([
                'projectId' => $project_id,
                'section' => $mirror_from_field_project_type_section_selector,
                'fields' => $mirror_from_field_project_type_section_field_selector
            ]));

            $params = [];
            if ($mirror_from_field_project_type_section_field_selector_type == "PersonLink") {
                if ($mirror_to_field_project_type_section_field_selector_type == "PersonList") {
                    $mirror_to_section_details = json_decode($filevine_api->getProjectFormsTeamInfo([
                        'projectId' => $project_id,
                        'section' => $mirror_to_field_project_type_section_selector,
                        'fields' => $mirror_to_field_project_type_section_field_selector
                    ]));
                    $mirror_to_field_data = $mirror_to_section_details->$mirror_to_field_project_type_section_field_selector;
                    $mirror_to_field_data[] = $section_details->$mirror_from_field_project_type_section_field_selector;
                    $params[$mirror_to_field_project_type_section_field_selector] = $mirror_to_field_data;
                }
            } else if ($mirror_from_field_project_type_section_field_selector_type == "PersonList") {
                if ($mirror_to_field_project_type_section_field_selector_type == "PersonLink") {
                    $mirror_from_field_data = $section_details->$mirror_from_field_project_type_section_field_selector;
                    if (count($mirror_from_field_data)) {
                        $params[$mirror_to_field_project_type_section_field_selector] = $mirror_from_field_data[0];
                    }
                }
            } else {
                $params[$mirror_to_field_project_type_section_field_selector] = $section_details->$mirror_from_field_project_type_section_field_selector;
            }

            $note_body = "Mirror data is empty!";
            if (count($params)) {
                $filevine_api->updateStaticForm($project_id, $mirror_to_field_project_type_section_selector, $params);
                $note_body = $action->action_name;
            }

            $log_data = [
                'tenant_id' => $tenant_id,
                'automated_workflow_log_id' => $log->id,
                'trigger_id' => $action->trigger_id,
                'action_id' => $action->id,
                'note_body' => $note_body,
                'fv_project_id' => $project_id,
                'fv_client_id' => $client_id
            ];

            AutomatedWorkflowActionLog::create($log_data);
            return true;
        } catch (Exception $e) {
            Logging::warning($e->getMessage());
        }
    }

    /**
     * Update Project Team
     */
    public static function updateProjectTeam($action, $log)
    {
        try {
            $tenant_id = $action->tenant_id;
            $request_json = json_decode($log->webhook_request_json);
            $project_id = (isset($request_json->ProjectId) && !empty($request_json->ProjectId)) ? $request_json->ProjectId : 0;
            $tenant_details = Tenant::where('id', $tenant_id)->first();
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
                $apiurl = $tenant_details->fv_api_base_url;
            }
            $filevine_api = new FilevineService($apiurl, "", $tenant_id);
            $project_details = json_decode($filevine_api->getProjectsById($project_id));
            $client_id = isset($project_details->clientId->native) ? $project_details->clientId->native : 0;

            $team_member_user_id = $action->team_member_user_id;
            if ($action->project_team_choice == 'Remove A Team Member') {
                $filevine_api->removeProjectTeamMember($project_id, $team_member_user_id);
            } else {
                $add_team_member_choice = $action->add_team_member_choice;
                $add_team_member_choice_level = $action->add_team_member_choice_level;
                $params['userId'] = [
                    'native' => $team_member_user_id
                ];
                if ($add_team_member_choice == 'Is Primary') {
                    $params['isPrimary'] = true;
                } else if ($add_team_member_choice == 'Is Admin') {
                    $params['isAdmin'] = true;
                } else if ($add_team_member_choice == 'Is First Primary') {
                    $params['isFirstPrimary'] = true;
                } else if ($add_team_member_choice == 'Level') {
                    $params['level'] = $add_team_member_choice_level;
                }
                Logging::warning(json_encode($params));
                $filevine_api->addProjectTeamMember($project_id, $params);
            }


            $log_data = [
                'tenant_id' => $tenant_id,
                'automated_workflow_log_id' => $log->id,
                'trigger_id' => $action->trigger_id,
                'action_id' => $action->id,
                'note_body' => $action->action_name,
                'fv_project_id' => $project_id,
                'fv_client_id' => $client_id
            ];

            AutomatedWorkflowActionLog::create($log_data);
            return true;
        } catch (Exception $e) {
            Logging::warning($e->getMessage());
        }
    }

    public static function updateMessageBody($message_body, $additional_data)
    {
        $variable_service = new VariableService();
        $message_body = $variable_service->updateVariables($message_body, 'is_automated_workflow_action', $additional_data);
        return $message_body;
    }
}
