<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Jobs\HandlePhaseChangedRequestJob;
use App\Models\Blacklist;
use Illuminate\Http\Request;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as Logging;
use Stichoza\GoogleTranslate\GoogleTranslate;

use App\Virtual\APIResponse;
use App\Models\Tenant;
use App\Models\TenantLive;
use App\Services\FilevineService;
use App\Models\WebhookSettings;
use App\Models\WebhookLogs;
use App\Models\AutoNotePhases;
use App\Models\AutoNoteOccurrences;
use App\Models\FvClients;
use App\Models\FvClientPhones;
use App\Models\LegalteamConfig;
use App\Models\AutoNoteGoogleReviewLinks;
use App\Models\AutoNoteGoogleReviewReplyMessages;
use App\Models\AutoNoteGoogleReview;
use Illuminate\Support\Facades\Route;
use App\Models\LanguageLog;
use App\Models\MassMessage;
use App\Models\MassMessageLog;
use App\Models\TenantNotificationConfig;

use App\Services\TwilioService;
use App\Services\NotificationHandlerService;
use App\Services\SMSLineService;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Services\VariableService;

class WebhookController extends Controller
{
    protected  $response;


    public function __construct(APIResponse $response)
    {
        $this->response = $response;
        Controller::setSubDomainName();
    }

    private function isBlacklisted($clientId, $getProjects = false)
    {
        if ($getProjects === false) {
            $blacklisted = Blacklist::where('fv_client_id', $clientId)->where('is_allow_notification', 0)->whereNull('fv_project_id')->count();
            if ($blacklisted > 0) {
                return "This client is not allowed! Please ask to the support team!";
            } else {
                return false;
            }
        } else {
            $projects = Blacklist::where('fv_client_id', $clientId)->where('is_allow_notification', 0)->get();
            if ($projects) {
                $projects = $projects->pluck('fv_project_id')->toArray();
            } else {
                $projects = [];
            }

            return $projects;
        }
    }

    /**
     * [POST] Create Contact Webhook
     */
    public function contact_created($domain, Request $request)
    {
        try {
            $tenant_details = Tenant::where('tenant_name', $domain)->first();
            if ($tenant_details) {
                if (isset($request->Object) && $request->Object == 'Contact' && isset($request->Event) && $request->Event == 'Created') {
                    $trigger = $request->Object . $request->Event;
                    $personId = $request->ObjectId['PersonId'];

                    // check client id.
                    if ($msg = $this->isBlacklisted($personId)) {
                        return $msg;
                    }

                    $orgId = $request->OrgId;
                    $apiurl = config('services.fv.default_api_base_url');
                    if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
                        $apiurl = $tenant_details->fv_api_base_url;
                    }
                    $obj = new FilevineService($apiurl, "");
                    $contact_details = $obj->getContactByContactId($personId);

                    $contact_details = json_decode($contact_details);

                    $data_array = array('payload_data' => $request, "contact_data" => $contact_details);

                    $values = array('tenant_id' => $tenant_details->id, 'trigger_action_name' => $trigger, 'fv_personId' => $personId, 'fv_org_id' => $orgId, 'webhook_route' => $request->path());
                    WebhookLogs::create($values);

                    // send that payload to destination url
                    $is_exist = WebhookSettings::where([
                        'trigger_action_name' => $trigger,
                        'tenant_id' => $tenant_details->id,
                        'is_active' => 1
                    ])
                        ->get();
                    if (count($is_exist) > 0) {
                        $url = $is_exist[0]->delivery_hook_url;
                        $ch = curl_init($url);
                        $data = json_encode($data_array);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $result = curl_exec($ch);
                        curl_close($ch);
                    } else {
                        return "Webhook Setting Invalid!";
                    }
                }
            } else {
                return "Tenant doesn't exist!!";
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [POST] Create Project Webhook
     */
    public function project_created($domain, Request $request)
    {
        try {
            $tenant_details = Tenant::where('tenant_name', $domain)->first();
            if ($tenant_details) {
                if (isset($request->Object) && $request->Object == 'Project' && isset($request->Event) && $request->Event == 'Created') {
                    $trigger = $request->Object . $request->Event;
                    $projectId = $request->ObjectId['ProjectId'];
                    $orgId = $request->OrgId;
                    $userId = $request->UserId;

                    $apiurl = config('services.fv.default_api_base_url');
                    if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
                        $apiurl = $tenant_details->fv_api_base_url;
                    }
                    $obj = new FilevineService($apiurl, "");
                    // check client id.
                    if ($msg = $this->isBlacklisted($userId)) {
                        return $msg;
                    }

                    // check project id.
                    if (in_array($projectId, $this->isBlacklisted($userId, true))) {
                        return "This project is not allowed! Please ask to the support team!";
                    }

                    $project_details = $obj->getProjectsDetailsById($projectId);

                    $project_details = json_decode($project_details);

                    $data_array = array('payload_data' => $request, "project_data" => $project_details);

                    $values = array('tenant_id' => $tenant_details->id, 'trigger_action_name' => $trigger, 'fv_projectId' => $projectId, 'fv_org_id' => $orgId, 'fv_userId' => $userId, 'webhook_route' => $request->path());
                    WebhookLogs::create($values);
                    // send that payload to destination url
                    $is_exist = WebhookSettings::where([
                        'trigger_action_name' => $trigger,
                        'tenant_id' => $tenant_details->id,
                        'is_active' => 1
                    ])
                        ->get();
                    if (count($is_exist) > 0) {
                        $url = $is_exist[0]->delivery_hook_url;
                        $ch = curl_init($url);
                        $data = json_encode($data_array);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $result = curl_exec($ch);
                        curl_close($ch);
                    } else {
                        return "Webhook Setting Invalid!";
                    }
                }
            } else {
                return "Tenant doesn't exist!!";
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [POST] Phase Changed Webhook
     * - Webhook setting handler.
     */
    public function phase_changed($domain, Request $request)
    {
        try {
            $tenant_details = Tenant::where('tenant_name', $domain)->first();
            if ($tenant_details) {
                if (isset($request->Object) && $request->Object == 'Project' && isset($request->Event) && $request->Event == 'PhaseChanged') {
                    $trigger = $request->Event;
                    $phaseName = $request->Other['PhaseName'];
                    $projectTypeId = $request->ObjectId['ProjectTypeId'];
                    $projectId = $request->ProjectId;
                    $phaseId = $request->ObjectId['PhaseId'];
                    $orgId = $request->OrgId;

                    $apiurl = config('services.fv.default_api_base_url');
                    if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
                        $apiurl = $tenant_details->fv_api_base_url;
                    }
                    $obj = new FilevineService($apiurl, "");
                    $project_type_details = $obj->getProjectTypeObject($projectTypeId);
                    $project_details = $obj->getProjectsDetailsById($projectId);

                    $project_type_details = json_decode($project_type_details);
                    $project_details = json_decode($project_details);

                    $project_type_name = "Personal Injury";

                    $data_array = array('payload_data' => $request, "project_data" => $project_details);

                    $values = array('tenant_id' => $tenant_details->id, 'trigger_action_name' => $trigger, 'phase_change_event' => $phaseName, 'item_change_type' => $project_type_name, 'fv_projectId' => $projectId, 'fv_phaseId' => $phaseId, 'fv_phaseName' => $project_type_name, 'fv_org_id' => $orgId, 'webhook_route' => $request->path());
                    WebhookLogs::create($values);

                    $is_exist = WebhookSettings::where([
                        'trigger_action_name' => $trigger,
                        'tenant_id' => $tenant_details->id,
                        'fv_phase_id' => $phaseId,
                        'is_active' => 1
                    ])->get();

                    if (count($is_exist) > 0) {
                        $condition = true;
                        foreach ($is_exist as $wh) {
                            $phaseChangeEvent = $wh->phase_change_event;
                            if (strtolower($phaseChangeEvent) == strtolower($phaseName)) {
                                $url = $wh->delivery_hook_url;
                                $ch = curl_init($url);
                                $data = json_encode($data_array);
                                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                $result = curl_exec($ch);
                                curl_close($ch);
                                break;
                            }
                        }
                    } else {
                        return "Webhook Setting Invalid!";
                    }
                } else {
                    return 'Invalid request';
                }
            } else {
                return "Tenant doesn't exist!!";
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [POST] Phase Changed Send Notification Webhook
     * - This is called when phase changed on settings page. : Phase Change SMS automated page.
     */
    public function send_notification_phase_changed($domain, Request $request)
    {
        try {

            $tenant_details = Tenant::where('tenant_name', $domain)->where('is_active', 1)->first();
            if ($tenant_details) {
                if (isset($request->Object) && $request->Object == 'Project' && isset($request->Event) && $request->Event == 'PhaseChanged') {
                    $trigger = $request->Event;
                    $phaseName = $request->Other['PhaseName'];
                    $projectTypeId = $request->ObjectId['ProjectTypeId'];
                    $projectId = $request->ProjectId;
                    $phaseId = $request->ObjectId['PhaseId'];
                    $orgId = $request->OrgId;

                    $apiurl = config('services.fv.default_api_base_url');
                    if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
                        $apiurl = $tenant_details->fv_api_base_url;
                    }
                    $obj = new FilevineService($apiurl, "");
                    $project_type_details = $obj->getProjectTypeObject($projectTypeId);
                    $project_details = $obj->getProjectsDetailsById($projectId);

                    $project_type_details = json_decode($project_type_details);
                    $project_details = json_decode($project_details, true);

                    // check client id.
                    if ($msg = $this->isBlacklisted($project_details['clientId']['native'])) {

                        // logging
                        $msg_obj = [
                            'event' => 'Phase Changed Event log',
                            'tenant_id' => $tenant_details->id,
                            'client_id' => $project_details['clientId']['native'],
                            'error' => 'Client is in blacklist.',
                            'phaseName' => $phaseName
                        ];
                        Logging::warning(json_encode($msg_obj));
                        return $msg;
                    }

                    // check project id.
                    if (in_array($projectId, $this->isBlacklisted($project_details['clientId']['native'], true))) {
                        $msg_obj = [
                            'event' => 'Phase Changed Event log',
                            'tenant_id' => $tenant_details->id,
                            'client_id' => $project_details['clientId']['native'],
                            'error' => 'This project is in blacklist.',
                            'phaseName' => $phaseName,
                            'projectId' => $projectId
                        ];
                        Logging::warning(json_encode($msg_obj));
                        return "This project is not allowed! Please ask to the support team!";
                    }

                    $project_type_name = "Personal Injury";

                    // checking if webhooklog is already exist with tenant_id and project_id and created_at is less than 5 mins and is_handled = 0
                    $phase_changed_check_mins = 5;
                    $existedLog = WebhookLogs::where([
                        'tenant_id' => $tenant_details->id,
                        'fv_projectId' => $projectId,
                        'webhook_route' => $request->path()
                    ])
                        ->where('created_at', '>=', Carbon::now()->subMinutes($phase_changed_check_mins))
                        ->latest()
                        ->first();


                    $createJob = true;
                    if ($existedLog) {
                        if ($existedLog->phase_change_event == $phaseName) {
                            $createJob = false;
                        } else {
                            $createJob = true;

                            // deleting that record from jobs table
                            DB::table('jobs')->where([
                                'tenant_id' => $tenant_details->id,
                                'fv_project_id' => $projectId,
                            ])->delete();
                        }
                    }

                    // set the log of the webhook request from Phase Changed.
                    $values = array('tenant_id' => $tenant_details->id, 'trigger_action_name' => $trigger, 'phase_change_event' => $phaseName, 'item_change_type' => $project_type_name, 'fv_projectId' => $projectId, 'fv_phaseId' => $phaseId, 'fv_phaseName' => $project_type_name, 'fv_org_id' => $orgId, 'webhook_route' => $request->path());
                    $wh = WebhookLogs::create($values);

                    // if not duplicated webhook of same phase changes
                    if ($createJob) {
                        $default_time = env('SMS_NOTE_BUFFER_DEFAULT_TIME', 300);
                        $config_details = DB::table('config')->where('tenant_id', $tenant_details->id)->first();
                        $delaySeconds = 10;
                        if ($config_details) {
                            if (isset($config_details->is_sms_buffer_time_enabled) && $config_details->is_sms_buffer_time_enabled == 1) {
                                $delaySeconds = (is_null($config_details->sms_buffer_time) || empty($config_details->sms_buffer_time)) ? $default_time : $config_details->sms_buffer_time;
                            }
                        }

                        // fetching job record and update with custom fields
                        $job_delay_time = now()->addSeconds($delaySeconds);
                        $job = (new HandlePhaseChangedRequestJob($wh, $request->all()))->delay($job_delay_time);
                        $jId = app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatch($job);
                        DB::table('jobs')
                            ->where('id', $jId)
                            ->update([
                                'tenant_id' => $tenant_details->id,
                                'fv_project_id' => $projectId,
                                'webhook_log_id' => $wh->id,
                            ]);

                        // logging of the PhaseChange SMS job creation.
                        $msg_obj = [
                            'event' => 'Phase Changed Event Job Created',
                            'tenant_id' => $tenant_details->id,
                            'fv_project_id' => $projectId,
                            'webhook_log_id' => $wh->id,
                            '$delaySeconds' => $delaySeconds,
                            '$job_delay_time' => $job_delay_time
                        ];
                        Logging::warning(json_encode($msg_obj));
                    } else {
                        // don't need to create the new one.
                        $msg_obj = [
                            'event' => 'Phase Changed Event duplicated: not created new job',
                            'tenant_id' => $tenant_details->id,
                            'projectId' => $projectId,
                            'client_id' => $project_details['clientId']['native'],
                            'phaseName' => $phaseName,
                            'project_type_name' => $project_type_name,
                        ];
                        Logging::warning(json_encode($msg_obj));
                    }
                } else {
                    $msg_obj = [
                        'event' => 'Phase Changed Event log',
                        'tenant_id' => $tenant_details->id,
                        'error' => 'Invalid request',
                        'request' => $request,
                    ];
                    Logging::warning(json_encode($msg_obj));
                    return 'Invalid request';
                }
            } else {
                $msg_obj = [
                    'event' => 'Phase Changed Event log',
                    'error' => 'Tenant does not exist!!',
                    'request' => $request,
                ];
                Logging::warning(json_encode($msg_obj));
                return "Tenant doesn't exist!!";
            }
        } catch (Exception $e) {
            // sent message log.
            $msg_obj = [
                'error' => 'Phase Change Job creation failed',
                'exception_err' => $e->getMessage()
            ];
            Logging::warning(json_encode($msg_obj));
            return $e->getMessage();
        }
    }

    /**
     * [POST] Send Notification Phase Changed Webhook
     * - This is called when phase changed on settings page. : Phase Change SMS page.
     * - don't use this function now. it is handled by job handler now.
     */
    public function send_notification_phase_changed_old($domain, Request $request)
    {
        try {

            // leave the request data in log file.
            Logging::warning(json_encode($request->all()));

            $tenant_details = Tenant::where('tenant_name', $domain)->first();
            if ($tenant_details) {
                if (isset($request->Object) && $request->Object == 'Project' && isset($request->Event) && $request->Event == 'PhaseChanged') {
                    $trigger = $request->Event;
                    $phaseName = $request->Other['PhaseName'];
                    $projectTypeId = $request->ObjectId['ProjectTypeId'];
                    $projectId = $request->ProjectId;
                    $phaseId = $request->ObjectId['PhaseId'];
                    $orgId = $request->OrgId;
                    $twi_sms_number_from = env('TWILIO_FROM');
                    $twi_sms_number_from_google_review_check = env('TWILIO_GOOGLE_REVIEW_CHECK_NUMBER');

                    $auto_note_exist = AutoNotePhases::where([
                        'phase_name' => $phaseName,
                        'is_active'  => 1,
                        'tenant_id'  => $tenant_details->id
                    ])->first();

                    $auto_note_occurrences = AutoNoteOccurrences::where([
                        'tenant_id'  => $tenant_details->id
                    ])->first();


                    if ($auto_note_occurrences) {
                        $apiurl = config('services.fv.default_api_base_url');
                        if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
                            $apiurl = $tenant_details->fv_api_base_url;
                        }
                        $obj = new FilevineService($apiurl, "");
                        $project_type_details = $obj->getProjectTypeObject($projectTypeId);
                        $project_details = $obj->getProjectsDetailsById($projectId);

                        $project_type_details = json_decode($project_type_details, true);
                        $project_details = json_decode($project_details, true);

                        // check client id.
                        if ($msg = $this->isBlacklisted($project_details['clientId']['native'])) {
                            return $msg;
                        }

                        // check project id.
                        if (in_array($projectId, $this->isBlacklisted($project_details['clientId']['native'], true))) {
                            return "This project is not allowed! Please ask to the support team!";
                        }

                        $client_name = "";

                        if ($project_details['clientName']) {
                            $client_name = explode(' ', $project_details['clientName']);
                            $client_name = $client_name[0];
                        }

                        $custom_message = null;
                        if (isset($auto_note_exist, $auto_note_exist->custom_message)) {
                            $custom_message = $this->generateCustomMessage($tenant_details, $auto_note_exist->custom_message, $client_name, $domain);
                        }

                        $zip_code = "";
                        $full_address = "";
                        $client_name = "";
                        if (isset($project_details['clientId']['native'])) {
                            $clientId = $project_details['clientId']['native'];
                            $client_obj = json_decode($obj->getContactByContactId($clientId), true);
                            if (isset($client_obj['phones']) && count($client_obj['phones']) > 0) {
                                $contact_phones = $client_obj['phones'];
                            }
                            if (isset($client_obj['addresses'])) {
                                if ($client_obj['addresses'][0]['postalCode']) {
                                    $zip_code = $client_obj['addresses'][0]['postalCode'];
                                    $full_address = $client_obj['addresses'][0]['fullAddress'];
                                }
                            }

                            if (isset($client_obj['fullName'])) {
                                $client_name = $client_obj['fullName'];
                            }

                            $client_firstname = $client_name;
                            if (isset($client_obj['firstName'])) {
                                $client_firstname = $client_obj['firstName'];
                            }

                            $fv_client = FvClients::where(['fv_client_id'  => $clientId, 'tenant_id' => $tenant_details->id])->first();
                            $is_new_fv_client = false;
                            $new_fv_client_tbl_id = null;
                            $current_date = date('Y-m-d H:i:s');
                            if (!$fv_client) {
                                // create a new fvClient.
                                $values = array(
                                    'tenant_id' => $tenant_details->id,
                                    'fv_client_id' => $clientId,
                                    'fv_client_name' => $client_name,
                                    'fv_client_address' => $full_address,
                                    'fv_client_zip' => $zip_code,
                                    'created_at' => $current_date,
                                    'updated_at' => $current_date
                                );
                                $last_fv_client = FvClients::create($values);
                                $fv_client = FvClients::where(['fv_client_id'  => $clientId, 'tenant_id' => $tenant_details->id])->first();
                                $is_new_fv_client = true;
                                $new_fv_client_tbl_id = isset($fv_client->id) ? $fv_client->id : null;
                            }

                            $twilio_api = new TwilioService();
                            $tenant_name = !empty($tenant_details->tenant_law_firm_name) ? $tenant_details->tenant_law_firm_name : $tenant_details->tenant_name;
                            $ask_message = "Hello " . $client_firstname . " - have you been happy with the service " . $tenant_name . " has provided? Please reply 'Yes' or 'No' only";

                            // send the custom message of the phase change and google review request
                            if (count($contact_phones) > 0) {
                                foreach ($contact_phones as $key => $phone) {
                                    $to_number = isset($phone['rawNumber']) ? $phone['rawNumber'] : null;
                                    if ($to_number == null) {
                                        continue;
                                    }
                                    // send custom message of the phase chagne.
                                    $TenantLive = TenantLive::where('tenant_id', $tenant_details->id)->where('status', 'live')->first();
                                    if (isset($auto_note_occurrences->is_on, $auto_note_occurrences->is_live) && $auto_note_occurrences->is_on == 1 && $auto_note_occurrences->is_live == 1 && $custom_message != null) {
                                        if (isset($TenantLive) and !empty($TenantLive)) {
                                            $msgresponse = $twilio_api->send_sms_message($to_number, $twi_sms_number_from, $custom_message);
                                            if (isset($msgresponse) and !empty($msgresponse)) {
                                                $array = [
                                                    'tenant_id' => $tenant_details->id,
                                                    'client_id' => $fv_client->id,
                                                    'message_id' => $msgresponse['sid'],
                                                    'message_body' => $custom_message,
                                                    'from_number' => $twi_sms_number_from_google_review_check,
                                                    'msg_type' => 'out',
                                                    'to_number' => $to_number,
                                                ];
                                                AutoNoteGoogleReviewReplyMessages::create($array);
                                            }
                                        }
                                        // sent message log.
                                        $msg_obj = ['Sent SMS to ' . $to_number, $custom_message];
                                        Logging::warning(json_encode($msg_obj));
                                    }

                                    // send google review request part.
                                    if (isset($auto_note_occurrences->google_review_is_on, $auto_note_occurrences->google_review_is_live) && $auto_note_occurrences->google_review_is_on == 1 && $auto_note_occurrences->google_review_is_live == 1) {
                                        if ((isset($auto_note_exist->is_send_google_review) && $auto_note_exist->is_send_google_review == 1 || $phaseName == 'Archived') && $zip_code != "") {
                                            if (isset($fv_client->is_google_review_response) && $fv_client->is_google_review_response == 1) {
                                                // find the phone numbers based on the label.
                                                $send_google_review =  $this->sendGoogleReivewLink($tenant_details->id, $zip_code, $to_number, $twi_sms_number_from_google_review_check);
                                            } else {
                                                // send the asking text to user.
                                                if (isset($TenantLive) and !empty($TenantLive)) {
                                                    // send the asking text to user.
                                                    $msgresponse = $twilio_api->send_sms_message($to_number, $twi_sms_number_from_google_review_check, $ask_message);
                                                    if (isset($msgresponse) && !empty($msgresponse)) {
                                                        $array = [
                                                            'tenant_id' => $tenant_details->id,
                                                            'client_id' => $fv_client->id,
                                                            'message_id' => $msgresponse['sid'],
                                                            'message_body' => $ask_message,
                                                            'from_number' => $twi_sms_number_from_google_review_check,
                                                            'msg_type' => 'out',
                                                            'is_google_review_filter_msg' => 1,
                                                            'to_number' => $to_number,
                                                        ];
                                                        AutoNoteGoogleReviewReplyMessages::create($array);
                                                    }
                                                }
                                            }
                                        } else {
                                            $msg_obj = [
                                                'send google review is failed',
                                                'is_send_google_review' => $auto_note_exist->is_send_google_review,
                                            ];
                                            Logging::warning(json_encode($msg_obj));
                                        }
                                    } else {
                                        $msg_obj = [
                                            'send google review is failed ',
                                            'google_review_is_on' => $auto_note_occurrences->google_review_is_on,
                                            'google_review_is_live' => $auto_note_occurrences->google_review_is_live,
                                        ];
                                        Logging::warning(json_encode($msg_obj));
                                    }

                                    if ($is_new_fv_client == true && $new_fv_client_tbl_id != null) {
                                        // add new fv client phones.
                                        $fv_client_phone_values = array('client_id' => $new_fv_client_tbl_id, 'client_phone' => $to_number, 'created_at' => $current_date, 'updated_at' => $current_date);
                                        FvClientPhones::create($fv_client_phone_values);
                                    }
                                }
                            }
                        }
                        $response = ['success' => true, 'message' => 'SMS Send Successfully'];
                        return json_encode($response);
                    } else {
                        $msg = "Phase Name doesn't exist";
                        Logging::warning($msg);
                        return $msg;
                    }
                } else {
                    $msg = 'Invalid request';
                    Logging::warning($msg);
                    return $msg;
                }
            } else {
                $msg = "Tenant doesn't exist!!";
                Logging::warning($msg);
                return $msg;
            }
        } catch (Exception $e) {
            Logging::warning(json_encode($e));
            return $e->getMessage();
        }
    }

    /**
     * Private Function To Get Custom Message
     */
    private function generateCustomMessage($tenant_details, $custom_message, $client_name, $domain)
    {
        $response = $custom_message;
        $tenant_name = !empty($tenant_details->tenant_law_firm_name) ? $tenant_details->tenant_law_firm_name : $tenant_details->tenant_name;
        $response = str_replace("[client_firstname]", $client_name, $response);
        $response = str_replace("[law_firm_name]", $tenant_name, $response);
        $response = str_replace("[client_portal_url]", route('client', ['subdomain' => $domain]), $response);
        return $response;
    }

    /**
     * Check if Phone Label is personal
     */
    public function checkIsPersonalPhoneLabel($phonelabel)
    {
        try {
            $phonelabel = mb_strtolower($phonelabel, 'UTF-8');
            $labels = ['phone', 'mobile', 'personal'];
            foreach ($labels as $key => $value) {
                if (strpos($phonelabel, $value) !== false) {
                    return true;
                }
            }
            return false;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [POST] Handle Mass Inbound Message
     */
    public function handle_mass_inbound_message(Request $request)
    {
        $client_phone_fv_format = $this->_normilizeToFVPhone($request->From);
        $is_exist = DB::table('mass_messages')
            ->join('mass_message_logs', 'mass_messages.id', '=', 'mass_message_logs.mass_message_id')
            ->select('mass_messages.tenant_id', 'mass_message_logs.person_name', 'mass_message_logs.person_number', 'mass_message_logs.job_id', 'mass_message_logs.note')
            ->where('mass_message_logs.person_number', $client_phone_fv_format)->orderBy('mass_message_logs.created_at', 'DESC')->first();
        if ($is_exist) {
            $MassMessage = MassMessage::create([
                'tenant_id' => $is_exist->tenant_id,
                'message_body' => $request->Body
            ]);
            MassMessageLog::create([
                'mass_message_id' => $MassMessage->id,
                'person_name' => $is_exist->person_name,
                'person_number' => $is_exist->person_number,
                'from_number' => $request->From,
                'job_id' => $is_exist->job_id,
                'is_inbound' => 1,
                'is_sent' => true,
                'sent_at' => Carbon::now()
            ]);

            // Call SMS Line Service
            new SMSLineService([
                "tenant_id" =>  $is_exist->tenant_id,
                'person_number' => $is_exist->person_number,
                'sms_line' => 'post_mass_text_response',
                'message_body' => $request->Body,
                "message_body_from_number" => $request->From,
                'message_note' => $is_exist->note,
            ]);

            // $twilio_api = new TwilioService();
            // $twi_sms_number_from = config('twilio.twilio.connections.twilio.mass_message');
            // $to_number = $request->From;
            // $message = "This SMS line is unmonitored. To speak with your Legal Team, please log into the Client Portal.";
            // $twilio_api->send_sms_message($to_number, $twi_sms_number_from, $message);

            // Send Admin Notification (Email and Post to Filevine)
            $notification_config = TenantNotificationConfig::where('tenant_id', $is_exist->tenant_id)->where('event_short_code', TenantNotificationConfig::SMSResponse)->first();
            if ($notification_config) {
                $params = [
                    'tenant_id' => $is_exist->tenant_id,
                    'note_body' => $request->Body,
                    'action_name' => 'SMS Response',
                ];
                NotificationHandlerService::callActionService($notification_config, $params);
            }

            $response = ['success' => true, 'message' => 'SMS send successfully'];
            return json_encode($response);
        } else {
            $response = ['success' => false, 'message' => 'The Outbound Does Not Exist!'];
            return json_encode($response);
        }
    }
    /**
     * [POST] Handle phase change Inbound Message
     */
    public function handle_phase_change_inbound_message(Request $request)
    {
        $client_phone_fv_format = $this->_normilizeToFVPhone($request->From);
        $is_exist = AutoNoteGoogleReviewReplyMessages::where('to_number', $client_phone_fv_format)->where('msg_type', 'out')->where('type_of_line', 'PhaseChange')->latest()->first();
        if ($is_exist) {
            if (!isset($is_exist->is_replied) || empty($is_exist->is_replied)) {
                $is_exist->update(['is_replied' => 1]);
            }
            AutoNoteGoogleReviewReplyMessages::create([
                "tenant_id" =>  $is_exist->tenant_id,
                "project_id" =>  $is_exist->project_id,
                "client_id" =>  $is_exist->client_id,
                "message_id" =>  $request->MessageSid,
                "message_body" => $request->Body,
                "from_number" => $request->From,
                "to_number" => $request->To,
                "msg_type" => "in",
                "type_of_line" => "PhaseChange"
            ]);

            // Call SMS Line Service
            new SMSLineService([
                "tenant_id" =>  $is_exist->tenant_id,
                "project_id" =>  $is_exist->project_id,
                'sms_line' => 'post_phase_change_response',
                'message_body' => $request->Body,
                "message_body_from_number" => $request->From,
                'client_id' => $is_exist->client_id,
                'message_note' => $is_exist->note,
            ]);


            // $twilio_api = new TwilioService();
            // $twi_sms_number_from = config('twilio.twilio.connections.twilio.phase_change');
            // $to_number = $request->From;
            // $message = "This SMS line is unmonitored. To speak with your Legal Team, please log into the Client Portal.";
            // $twilio_api->send_sms_message($to_number, $twi_sms_number_from, $message);

            // Send Admin Notification (Email and Post to Filevine)
            $notification_config = TenantNotificationConfig::where('tenant_id', $is_exist->tenant_id)->where('event_short_code', TenantNotificationConfig::SMSResponse)->first();
            if ($notification_config) {
                $params = [
                    'project_id' => $is_exist->project_id,
                    'tenant_id' => $is_exist->tenant_id,
                    'client_id' => $is_exist->client_id,
                    'note_body' => $request->Body,
                    'action_name' => 'SMS Response',
                ];
                NotificationHandlerService::callActionService($notification_config, $params);
            }

            $response = ['success' => true, 'message' => 'SMS send successfully'];
            return json_encode($response);
        } else {
            $response = ['success' => false, 'message' => 'The Outbound Does Not Exist!'];
            return json_encode($response);
        }
    }
    /**
     * [POST] Handle two factor authentication Inbound Message
     */
    public function handle_two_fa_inbound_message(Request $request)
    {
        $client_phone_fv_format = $this->_normilizeToFVPhone($request->From);
        $is_exist = AutoNoteGoogleReviewReplyMessages::where('to_number', $client_phone_fv_format)->where('msg_type', 'out')->latest()->first();
        if ($is_exist) {
            if (!isset($is_exist->is_replied) || empty($is_exist->is_replied)) {
                $is_exist->update(['is_replied' => 1]);
            }
            AutoNoteGoogleReviewReplyMessages::create([
                "tenant_id" =>  $is_exist->tenant_id,
                "project_id" =>  $is_exist->project_id,
                "client_id" =>  $is_exist->client_id,
                "message_id" =>  $request->MessageSid,
                "message_body" => $request->Body,
                "from_number" => $request->From,
                "to_number" => $request->To,
                "msg_type" => "in",
                "type_of_line" => "2FAVerification"
            ]);

            // $twilio_api = new TwilioService();
            // $twi_sms_number_from = config('twilio.twilio.connections.twilio.from');
            // $to_number = $request->From;
            // $message = "This SMS line is unmonitored. To speak with your Legal Team, please log into the Client Portal.";
            // $twilio_api->send_sms_message($to_number, $twi_sms_number_from, $message);

            // Send Admin Notification (Email and Post to Filevine)
            $notification_config = TenantNotificationConfig::where('tenant_id', $is_exist->tenant_id)->where('event_short_code', TenantNotificationConfig::SMSResponse)->first();
            if ($notification_config) {
                $params = [
                    'project_id' => $is_exist->project_id,
                    'tenant_id' => $is_exist->tenant_id,
                    'client_id' => $is_exist->client_id,
                    'note_body' => $request->Body,
                    'action_name' => 'SMS Response',
                ];
                NotificationHandlerService::callActionService($notification_config, $params);
            }

            $response = ['success' => true, 'message' => 'SMS send successfully'];
            return json_encode($response);
        } else {
            $response = ['success' => false, 'message' => 'The Outbound Does Not Exist!'];
            return json_encode($response);
        }
    }

    /**
     * [POST] Handle Webhook Message
     * - This is called now.
     */
    public function handle_inbound_message(Request $request)
    {
        try {
            if (isset($request->From) && isset($request->To)) {
                $this->updateClientPhoneCommunication($request->From, $request->Body);
                if ($request->To == config('twilio.twilio.connections.twilio.phase_change')) {
                    return $this->handle_phase_change_inbound_message($request);
                } else if ($request->To == config('twilio.twilio.connections.twilio.review_request')) {
                    return $this->handle_google_review_check_inbound_message($request);
                } else if ($request->To == config('twilio.twilio.connections.twilio.mass_message')) {
                    return $this->handle_mass_inbound_message($request);
                } else if ($request->To == config('twilio.twilio.connections.twilio.from')) {
                    return $this->handle_two_fa_inbound_message($request);
                }
            } else {
                return 'Invalid request';
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Send Google Review Link to user
     */
    public function sendGoogleReivewLink($tenant_id, $zip_code, $to_number, $twi_sms_number_from, $fv_client = null, $google_review_msg = null, $additional_data = null)
    {
        try {
            $google_review_link = DB::table('auto_note_google_review_links')
                ->join('auto_note_google_review_cities', 'auto_note_google_review_links.id', '=', 'auto_note_google_review_cities.auto_note_google_review_link_id')
                ->select('auto_note_google_review_links.*', 'auto_note_google_review_cities.auto_note_google_review_link_id', 'auto_note_google_review_cities.zip_code')
                ->where('auto_note_google_review_links.tenant_id', '=', $tenant_id)
                ->where('auto_note_google_review_cities.zip_code', '=', $zip_code)
                ->first();

            if (!$google_review_link) {
                // get the default google review link.
                $google_review_link = AutoNoteGoogleReviewLinks::where(['tenant_id' => $tenant_id, 'is_default' => 1])->first();
            }
            //  $TenantLive = TenantLive::where('tenant_id',$tenant_id)->where('status','live')->first();
            if ($google_review_link && isset($google_review_link->review_link)) {
                $twilio_api = new TwilioService();

                $review_link = $google_review_link->review_link;
                if (empty($google_review_msg)) {
                    $message = 'Great! Please leave us a Google Review about your 5-Star experience with our firm here:';
                    $google_review_msg = $message . ' ' . $review_link;
                } else {
                    $variable_service = new VariableService();
                    $additional_data['review_link'] = $review_link;
                    $google_review_msg = $variable_service->updateVariables($google_review_msg, 'is_review_request_sms', $additional_data);
                }

                // Find client language and translate
                $language = LanguageLog::where('tenant_id', $tenant_id)->where('fv_client_id', $fv_client->fv_client_id)->orderBy('updated_at', 'DESC')->first();
                $latest_language = 'en';
                if ($language != null) {
                    $latest_language = $language->language;
                }
                $tr = new GoogleTranslate();
                $tr->setSource();
                $tr->setTarget($latest_language);
                $google_review_msg = $tr->translate($google_review_msg);


                //     if (isset($TenantLive) and !empty($TenantLive)) {
                $msgresponse = $twilio_api->send_sms_message($to_number, $twi_sms_number_from, $google_review_msg);
                if (isset($msgresponse) and !empty($msgresponse)) {
                    $array = [
                        'tenant_id' => $tenant_id,
                        'client_id' => isset($fv_client->id) ? $fv_client->id : 0,
                        'message_id' => $msgresponse['sid'],
                        'message_body' => $google_review_msg,
                        'from_number' => $twi_sms_number_from,
                        'msg_type' => 'out',
                        'type_of_line' => "ReviewRequest",
                        'to_number' => $to_number,
                    ];
                    AutoNoteGoogleReviewReplyMessages::create($array);
                }
                //  }

                // sent message log.
                $msg_obj = [
                    'event' => 'Google Review Request Sent SMS',
                    'tenant_id' => $tenant_id,
                    'to_number' => $to_number,
                    'google reivew link' => $google_review_link->review_link
                ];
                Logging::warning(json_encode($msg_obj));
            } else {
                // sent message log.
                $msg_obj = [
                    'error' => 'google review send failed.',
                    'tenant_id' => $tenant_id,
                    'client_id' => $fv_client->id ?? "",
                    'to_number' => $to_number,
                    'google reivew link' => $google_review_link
                ];
                Logging::warning(json_encode($msg_obj));
            }
        } catch (Exception $e) {
            // sent message log.
            $msg_obj = [
                'error' => 'google review send failed.',
                'tenant_id' => $tenant_id,
                'client_id' => $fv_client->id ?? "",
                'to_number' => $to_number,
                'exception_err' => $e->getMessage()
            ];
            Logging::warning(json_encode($msg_obj));
            return $e->getMessage();
        }
    }

    public function handleInboundMessage($from, $tenant_law_firm_name = "", $tenant_id, $client_id)
    {
        try {
            if (isset($from)) {
                $twilio_api = new TwilioService();

                // Find client language and translate
                $language = LanguageLog::where('tenant_id', $tenant_id)->where('fv_client_id', $client_id)->orderBy('updated_at', 'DESC')->first();
                $latest_language = 'en';
                if ($language != null) {
                    $latest_language = $language->language;
                }
                $tr = new GoogleTranslate();
                $tr->setSource();
                $tr->setTarget($latest_language);

                $twi_sms_number_from = env('TWILIO_FROM_REVIEW_REQUEST');
                $to_number = $from;
                $message = "This SMS line is monitored by " . $tenant_law_firm_name . ". We will pass your message along to your assigned team.";
                $message = $tr->translate($message);
                $twresponse = $twilio_api->send_sms_message($to_number, $twi_sms_number_from, $message);

                $response = ['success' => true, 'message' => 'SMS send successfully'];
                return ['message' => $message, 'tw' => $twresponse];
            } else {
                return 'Invalid request';
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [POST] Handle Google Review Inbound message
     * - This is called from Twilio Webhook when the client answer with Yes/No for the Google review request asking messages.
     */
    public function handle_google_review_check_inbound_message(Request $request)
    {
        try {
            if (isset($request->From) && isset($request->Body)) {
                $twilio_api = new TwilioService();

                $twi_sms_number_from = config('twilio.twilio.connections.twilio.review_request');
                $to_phone = $request->To;
                $client_phone = $request->From; // ex: "+15017122661"
                $body = $request->Body;
                $message_id = $request->MessageSid;
                $sms_line_service_stop_client_reply = false;


                // get the reformated client_phone. ex: "+15017122661" => "5017122661"
                $client_phone_fv_format = $this->_normilizeToFVPhone($client_phone);
                $AutoNoteGoogleReviewReplyMessages = AutoNoteGoogleReviewReplyMessages::where('to_number', $client_phone_fv_format)->where('is_google_review_filter_msg', 1)->latest()->first();


                // If reply come from project sms number or mailroom number then call SMS line service and return
                if (isset($AutoNoteGoogleReviewReplyMessages->note) && ($AutoNoteGoogleReviewReplyMessages->note == 'sendToProjectSMSNumber' || $AutoNoteGoogleReviewReplyMessages->note == 'sendToMailroom')) {
                    // Call SMS Line Service
                    new SMSLineService([
                        "tenant_id" =>  $AutoNoteGoogleReviewReplyMessages->tenant_id,
                        "project_id" =>  $AutoNoteGoogleReviewReplyMessages->project_id,
                        'sms_line' => 'post_review_request_response',
                        'message_body' => $body,
                        "message_body_from_number" => $client_phone,
                        'client_id' => $AutoNoteGoogleReviewReplyMessages->client_id,
                        'stop_client_reply' => $sms_line_service_stop_client_reply,
                        'message_note' => $AutoNoteGoogleReviewReplyMessages->note,
                    ]);
                    return;
                }

                if (isset($AutoNoteGoogleReviewReplyMessages) and !empty($AutoNoteGoogleReviewReplyMessages)) {
                    $tenant_id = $AutoNoteGoogleReviewReplyMessages->tenant_id;
                    $TenantLive = TenantLive::where('tenant_id', $tenant_id)->where('status', 'live')->first();
                    $project_id = $AutoNoteGoogleReviewReplyMessages->project_id;

                    $tenant_details = Tenant::where('id', $tenant_id)->first();
                    $tenant_law_firm_name = "";
                    if (isset($tenant_details->tenant_law_firm_name) and !empty($tenant_details->tenant_law_firm_name)) {
                        $tenant_law_firm_name = $tenant_details->tenant_law_firm_name;
                    }
                    $apiurl = config('services.fv.default_api_base_url');
                    if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
                        $apiurl = $tenant_details->fv_api_base_url;
                    }
                    $filevine_api = new FilevineService($apiurl, "", $tenant_id);
                    $fv_client_obj = FvClients::where(['id'  => $AutoNoteGoogleReviewReplyMessages->client_id])->first();


                    // save the inbound message yes/no or 0-5 rating in logs table.
                    $array = [
                        'tenant_id' => $tenant_id,
                        'client_id' => $fv_client_obj->id,
                        'message_id' => $message_id,
                        'message_body' => $body,
                        'from_number' => $client_phone_fv_format,
                        'msg_type' => 'in',
                        'type_of_line' => 'ReviewRequest',
                        'to_number' => $to_phone,
                    ];
                    AutoNoteGoogleReviewReplyMessages::create($array);

                    // store the inbound message to FV Project SMS or FV note.
                    // get the FV project SMS number.
                    $project_vitals = json_decode($filevine_api->getProjectsVitalsById($project_id));
                    $project_sms_number = "";
                    if (!empty($project_vitals)) {
                        foreach ($project_vitals as $vital) {
                            if (isset($vital->fieldName) && $vital->fieldName == 'projectSmsNumber') {
                                $project_sms_number = !empty($vital->value) ? $vital->value : "";
                            }
                        }
                    }
                    $forward_msg = "A text message from " . $client_phone . " as been received in VineConnect Client Portal: " . $body . ". Timestamp: " . date("Y-m-d H:i:s");

                    // Find client language and translate
                    $language = LanguageLog::where('tenant_id', $tenant_id)->where('fv_client_id', $fv_client_obj->fv_client_id)->orderBy('updated_at', 'DESC')->first();
                    $latest_language = 'en';
                    if ($language != null) {
                        $latest_language = $language->language;
                    }
                    $tr = new GoogleTranslate();
                    $tr->setSource();
                    $tr->setTarget($latest_language);
                    $forward_msg = $tr->translate($forward_msg);

                    if (!empty($project_sms_number)) {
                        // send Twilio SMS
                        $msgresponse = $twilio_api->send_sms_message($project_sms_number, $twi_sms_number_from, $forward_msg);
                        if (isset($msgresponse) and !empty($msgresponse)) {
                            $array = [
                                'tenant_id' => $tenant_id,
                                'client_id' => $fv_client_obj->id,
                                'message_id' => isset($msgresponse['sid']) ? $msgresponse['sid'] : '',
                                'message_body' => $forward_msg,
                                'from_number' => $twi_sms_number_from,
                                'msg_type' => 'out',
                                'type_of_line' => 'ReviewRequest',
                                'to_number' => $this->_normilizeToFVPhone($project_sms_number)
                            ];
                            AutoNoteGoogleReviewReplyMessages::create($array);
                        }
                        $msg = 'sent forward reply message to FV project SMS number.';
                        Logging::warning($msg);
                    } else {
                        // send the note to FV project.
                        // get the author ID from the FV author id.
                        $clientNativeId = null;
                        $auth_user_obj = json_decode($filevine_api->getAuthUser(), TRUE);
                        if ($auth_user_obj && isset($auth_user_obj['user'])) {
                            if (isset($auth_user_obj['user']['userId']) && isset($auth_user_obj['user']['userId']['native'])) {
                                $clientNativeId = $auth_user_obj['user']['userId']['native'];
                            }
                        }
                        if (isset($clientNativeId) && $clientNativeId != null) {
                            $params = [
                                'projectId' => ['native' => $project_id, 'partner' => null],
                                'body' => $body . " - Sent by " . $client_phone_fv_format . " on " . date("Y-m-d H:i:s"),
                                'authorId' => ['native' => $clientNativeId, 'partner' => null]
                            ];

                            $resp = $filevine_api->createNote($params);
                            Logging::warning("Create Note");
                            Logging::warning($resp);
                        }
                    }

                    if (!isset($AutoNoteGoogleReviewReplyMessages['is_replied']) || empty($AutoNoteGoogleReviewReplyMessages['is_replied'])) {
                        $AutoNoteGoogleReviewReplyMessages->update(['is_replied' => 1]);

                        $autoNoteGoogleReview = AutoNoteGoogleReview::where('tenant_id', $tenant_id)->first();
                        if ($autoNoteGoogleReview != null) {
                            if ($autoNoteGoogleReview->is_set_qualified_response_threshold) {
                                $minimum_score = $autoNoteGoogleReview->minimum_score;
                                $message_body_score = (int) filter_var($body, FILTER_SANITIZE_NUMBER_INT);
                                if ($message_body_score >= $minimum_score) {
                                    $tenant_name = !empty($tenant_details->tenant_law_firm_name) ? $tenant_details->tenant_law_firm_name : $tenant_details->tenant_name;
                                    $client_firstname = isset($fv_client_obj->fv_client_name) ? $fv_client_obj->fv_client_name : '';
                                    $review_request_text_body = $autoNoteGoogleReview->review_request_text_body;

                                    $additional_data = [
                                        'tenant_id' => $tenant_id,
                                        'client_firstname' => $client_firstname,
                                        'law_firm_name' => $tenant_name,
                                        'fv_project_id' => $project_id
                                    ];

                                    $values = array('is_google_review_response' =>  1);
                                    $fv_client_obj->update($values);
                                    $zip_code = isset($fv_client_obj->fv_client_zip) ? $fv_client_obj->fv_client_zip : '';
                                    $this->sendGoogleReivewLink($tenant_id, $zip_code, $client_phone, $twi_sms_number_from, $fv_client_obj, $review_request_text_body, $additional_data);
                                    Logging::warning('sendGoogleReivewLink done');

                                    // Send Admin Notification (Email and Post to Filevine)
                                    $notification_config = TenantNotificationConfig::where('tenant_id', $tenant_id)->where('event_short_code', TenantNotificationConfig::GoogleReviewThreshold)->first();
                                    if ($notification_config) {
                                        $params = [
                                            'project_id' => $project_id,
                                            'tenant_id' => $tenant_id,
                                            'client_id' => $fv_client_obj->fv_client_id,
                                            'note_body' => $body,
                                            'action_name' => 'Google Review Threshold Met',
                                        ];
                                        NotificationHandlerService::callActionService($notification_config, $params);
                                    }

                                    $sms_line_service_stop_client_reply = true;
                                } else {
                                    if ($autoNoteGoogleReview->is_send_unqualified_response_request) {
                                        $message = $autoNoteGoogleReview->unqualified_review_request_msg_body;
                                        $message = $tr->translate($message);
                                        $msgresponse = $twilio_api->send_sms_message($client_phone, $twi_sms_number_from, $message);
                                        if (isset($msgresponse) and !empty($msgresponse)) {
                                            $array = [
                                                'tenant_id' => $tenant_id,
                                                'client_id' => $fv_client_obj->id,
                                                'message_id' => isset($msgresponse['sid']) ? $msgresponse['sid'] : '',
                                                'message_body' => $message,
                                                'from_number' => $twi_sms_number_from,
                                                'msg_type' => 'out',
                                                'type_of_line' => 'ReviewRequest',
                                                'to_number' => $client_phone_fv_format,
                                            ];
                                            AutoNoteGoogleReviewReplyMessages::create($array);
                                        }
                                        $msg = 'unqualified message done';
                                        Logging::warning($msg);

                                        $sms_line_service_stop_client_reply = true;
                                    }
                                }
                            }
                        }
                    }

                    // Call SMS Line Service
                    new SMSLineService([
                        "tenant_id" =>  $tenant_id,
                        "project_id" =>  $project_id,
                        'sms_line' => 'post_review_request_response',
                        'message_body' => $body,
                        "message_body_from_number" => $client_phone,
                        'client_id' => $fv_client_obj->id,
                        'stop_client_reply' => $sms_line_service_stop_client_reply,
                        'message_note' => $AutoNoteGoogleReviewReplyMessages->note,
                    ]);
                } else {
                    $msg = 'Invalid FV Client Phone';
                    Logging::warning($msg);
                    return $msg;
                }

                $response = ['success' => true, 'message' => 'SMS send successfully'];
                return json_encode($response);
            } else {
                $msg = 'Invalid request';
                Logging::warning($msg);
                return $msg;
            }
        } catch (Exception $e) {
            Logging::warning(json_encode($e));
            return $e->getMessage();
        }
    }


    /**
     * [POST] Create CollectionItem Webhook
     */
    public function collectionItemCreated($domain, Request $request)
    {
        try {
            /*
            {
                "Timestamp":1635900286245,
                "Object":"CollectionItem",
                "Event":"Created",
                "ObjectId":{
                    "ProjectTypeId":14090,
                    "SectionSelector":"research",
                    "ItemId":"a0c52e4b-c939-4a62-b5f7-798f479a788f"
                },
                "OrgId":5322,
                "ProjectId":5916006,
                "UserId":20704,
                "Other":[]
            }
            */
            // leave the request data in log file.
            // Logging::warning(json_encode($request->all()));

            $tenant_details = Tenant::where('tenant_name', $domain)->first();
            if ($tenant_details) {
                if (isset($request->Object) && $request->Object == 'CollectionItem' && isset($request->Event) && ($request->Event == 'Created' || $request->Event == 'Updated')) {
                    $trigger = $request->Object;
                    $trigger_action_event = $request->Event;
                    $ProjectTypeId = $request->ObjectId['ProjectTypeId'];
                    $projectId = $request->ProjectId;
                    $orgId = $request->OrgId;
                    $userId = $request->UserId;
                    $object = $request->Object;
                    $event = $request->Event;
                    $itemId = $request->ObjectId['ItemId'];
                    $sectionSelector = $request->ObjectId['SectionSelector'];

                    $apiurl = config('services.fv.default_api_base_url');
                    if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
                        $apiurl = $tenant_details->fv_api_base_url;
                    }
                    $obj = new FilevineService($apiurl, "");
                    $project_details = $obj->getProjectsDetailsById($projectId);
                    $project_details = json_decode($project_details);

                    $collection_params = array(
                        'projectId' => $projectId,
                        'sectionSelector' => $sectionSelector,
                        'uniqueId' => $itemId,
                        // 'query_param' => '?requestedFields=firstname,lastname'
                    );
                    $collection_details = $obj->getCollectionItem($collection_params);
                    $collection_details = json_decode($collection_details, true);

                    $data_array = array(
                        'payload_data' => $request->all(),
                        'project_data' => $project_details,
                        'collection_data' => $collection_details
                    );

                    $values = array(
                        'tenant_id' => $tenant_details->id,
                        'trigger_action_name' => $trigger . $trigger_action_event,
                        'fv_projectId' => $projectId,
                        'fv_org_id' => $orgId,
                        'fv_userId' => $userId,
                        'fv_object' => $object,
                        'fv_event' => $event,
                        'fv_object_id' => $itemId
                    );
                    WebhookLogs::create($values);

                    // send that payload to destination url
                    $webhook_setting = WebhookSettings::where([
                        'trigger_action_name' => $trigger,
                        'trigger_action_event' => $trigger_action_event,
                        'tenant_id' => $tenant_details->id,
                        'fv_project_type_id' => $ProjectTypeId,
                        'collection_item_name' => $sectionSelector,
                        'is_active' => 1
                    ])->first();

                    if (isset($webhook_setting, $webhook_setting->delivery_hook_url) && $webhook_setting != null) {
                        $url = $webhook_setting->delivery_hook_url;
                        $ch = curl_init($url);
                        $data = json_encode($data_array);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $result = curl_exec($ch);
                        curl_close($ch);
                        return "Success!";
                    } else {
                        return "Webhook Setting Invalid!";
                    }
                } else {
                    return "Trigger Action Name/Event Does not Exist!";
                }
            } else {
                return "Tenant doesn't exist!!";
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [POST] Create Task Webhook
     */
    public function taskCreated($domain, Request $request)
    {
        /* {
            "Timestamp":1647883798588,
            "Object":"Note",
            "Event":"Created",
            "ObjectId":{
                "NoteId":252530628
            },
            "OrgId":8564,
            "ProjectId":9753977,
            "UserId":null,
            "Other":[
            ]
            }  */
        try {
            // leave the request data in log file.
            // Logging::warning(json_encode($request->all()));

            $tenant_details = Tenant::where('tenant_name', $domain)->first();
            if ($tenant_details) {
                if (isset($request->Object) && $request->Object == 'Note' && isset($request->Event) && ($request->Event == 'Created' || $request->Event == 'Completed')) {
                    $trigger = $request->Object;
                    $trigger_action_event = $request->Event;
                    $projectId = $request->ProjectId;
                    $orgId = $request->OrgId;
                    $userId = $request->UserId;
                    $object = $request->Object;
                    $event = $request->Event;
                    $noteId = $request->ObjectId['NoteId'];

                    $apiurl = config('services.fv.default_api_base_url');
                    if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
                        $apiurl = $tenant_details->fv_api_base_url;
                    }
                    $obj = new FilevineService($apiurl, "");
                    $project_details = $obj->getProjectsDetailsById($projectId);
                    $project_details = json_decode($project_details, true);

                    $task_details = $obj->getTaskById($noteId);
                    if ($task_details != null) {
                        $task_details = json_decode($task_details, true);
                    }

                    $data_array = array('payload_data' => $request->all(), "project_data" => $project_details, "task_data" => $task_details);

                    $values = array('tenant_id' => $tenant_details->id, 'trigger_action_name' => $trigger . $trigger_action_event, 'fv_projectId' => $projectId, 'fv_org_id' => $orgId, 'fv_userId' => $userId, 'fv_object' => $object, 'fv_event' => $event, 'fv_object_id' => $noteId);
                    WebhookLogs::create($values);
                    // send that payload to destination url
                    $is_exist = WebhookSettings::where([
                        'trigger_action_name' => $trigger,
                        'trigger_action_event' => $trigger_action_event,
                        'tenant_id' => $tenant_details->id,
                        'is_active' => 1
                    ])->get();
                    if (count($is_exist) > 0) {
                        foreach ($is_exist as $wh) {
                            $task_filter_name = $wh->task_filter_name;
                            // if task contains the taskTag from webhook.
                            if (isset($task_details->typeTag) && (strpos(strtolower($task_details->typeTag), strtolower($task_filter_name)) !== false)) {
                                $url = $wh->delivery_hook_url;
                                $ch = curl_init($url);
                                $data = json_encode($data_array);
                                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                $result = curl_exec($ch);
                                curl_close($ch);
                                break;
                            }
                        }
                        return 'Success!';
                    } else {
                        return "Webhook Setting Invalid!";
                    }
                } else {
                    return "Trigger Action Name/Event Does not Exist!";
                }
            } else {
                return "Tenant doesn't exist!!";
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    public function updateClientPhoneCommunication($client_phone, $message_body)
    {
        if (strtolower(trim($message_body)) == 'stop') {
            FvClientPhones::where('client_phone', $client_phone)->update(['auto_communication_stop_at' => Carbon::now()]);
        }

        if (strtolower(trim($message_body)) == 'start') {
            FvClientPhones::where('client_phone', $client_phone)->update(['auto_communication_start_at' => Carbon::now()]);
        }
    }


    public function receiveTest($domain, Request $request)
    {
        file_put_contents(storage_path() . "/curl" . rand() . date('Y-m-d-H-i-s') . ".txt", json_encode($request->all()));
    }
}
