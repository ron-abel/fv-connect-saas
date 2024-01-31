<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log as Logging;
use Illuminate\Support\Facades\DB;
use Exception;

use App\Services\FilevineService;
use App\Services\TwilioService;

use App\Models\WebhookLogs;
use App\Models\WebhookSettings;
use Carbon\Carbon;
use Stichoza\GoogleTranslate\GoogleTranslate;
use App\Models\AutoNoteGoogleReviewLinks;
use App\Models\FvClientPhones;
use App\Models\AutoNotePhases;
use App\Models\FvClients;
use App\Models\AutoNoteOccurrences;
use App\Models\LanguageLog;
use App\Models\Tenant;
use App\Models\AutoNoteGoogleReviewReplyMessages;
use App\Models\AutoNoteGoogleReview;
use App\Models\TenantLive;
use App\Services\VariableService;
use App\Services\SMSTimezoneHandleService;


class HandlePhaseChangedRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var WebhookLogs
     */
    private $wh;
    private $request;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(WebhookLogs $wh, $request)
    {
        $this->wh = $wh;
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $wh = $this->wh;
            $tenant_details = Tenant::where('id', $wh->tenant_id)->first();
            $project_id = $wh->fv_projectId;
            $domain = $tenant_details->tenant_name;
            $phaseName = $wh->phase_change_event;
            $fv_phaseId = $wh->fv_phaseId;
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
                $apiurl = $tenant_details->fv_api_base_url;
            }
            $obj = new FilevineService($apiurl, "", $wh->tenant_id);
            $project_details = $obj->getProjectsDetailsById($wh->fv_projectId);
            $project_details = json_decode($project_details, true);

            $data_array = ['payload_data' => $this->request, "project_data" => $project_details];

            // Start Messaging
            // this is used for checking google review link handler.
            $auto_note_exist = AutoNotePhases::where([
                'fv_phase_id' => $fv_phaseId,
                'tenant_id'  => $tenant_details->id
            ])->first();

            $auto_note_occurrences = AutoNoteOccurrences::where([
                'tenant_id'  => $tenant_details->id
            ])->first();

            $twi_sms_number_from = config('twilio.twilio.connections.twilio.phase_change');
            $twi_sms_number_from_google_review_check = config('twilio.twilio.connections.twilio.review_request');
            if ($auto_note_occurrences) {
                $client_name = "";
                if (isset($project_details['clientName'])) {
                    $client_name = explode(' ', $project_details['clientName']);
                    $client_name = $client_name[0];
                }

                $auto_note_exist_custom_sms = AutoNotePhases::where([
                    'fv_phase_id' => $fv_phaseId,
                    'is_active' => 1,
                    'tenant_id'  => $tenant_details->id
                ])->first();
                $custom_message = null;
                if (isset($auto_note_exist_custom_sms, $auto_note_exist_custom_sms->custom_message)) {
                    $custom_message = $this->generateCustomMessage($tenant_details, $auto_note_exist_custom_sms->custom_message, $client_name, $domain, $project_id);
                }

                $zip_code = "";
                $full_address = "";
                $client_name = "";
                $contact_phones = [];
                if (isset($project_details['clientId']['native'])) {
                    $clientId = $project_details['clientId']['native'];
                    $client_obj = json_decode($obj->getContactByContactId($clientId), true);
                    if (isset($client_obj['phones']) && count($client_obj['phones']) > 0) {
                        $contact_phones = $client_obj['phones'];
                    }

                    if (isset($client_obj['addresses'])) {
                        if (isset($client_obj['addresses'][0]['postalCode'])) {
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
                    } else {
                        FvClients::where('id', $fv_client->id)->update([
                            'fv_client_name' => $client_name,
                            'fv_client_address' => $full_address,
                            'fv_client_zip' => $zip_code,
                            'updated_at' => $current_date
                        ]);
                    }


                    // Find review link and set ask message content
                    $google_review_link = DB::table('auto_note_google_review_links')
                        ->join('auto_note_google_review_cities', 'auto_note_google_review_links.id', '=', 'auto_note_google_review_cities.auto_note_google_review_link_id')
                        ->select('auto_note_google_review_links.*', 'auto_note_google_review_cities.auto_note_google_review_link_id', 'auto_note_google_review_cities.zip_code')
                        ->where('auto_note_google_review_links.tenant_id', '=', $tenant_details->id)
                        ->where('auto_note_google_review_cities.zip_code', '=', $zip_code)
                        ->first();
                    // If not found get the default google review link.
                    if (!($google_review_link && isset($google_review_link->review_link))) {
                        $google_review_link = AutoNoteGoogleReviewLinks::where(['tenant_id' => $tenant_details->id, 'is_default' => 1])->first();
                    }
                    $review_link = isset($google_review_link->review_link) ? $google_review_link->review_link : '';

                    $autoNoteGoogleReview = AutoNoteGoogleReview::where('tenant_id', $tenant_details->id)->first();
                    if ($autoNoteGoogleReview == null) {
                        $ask_message = "Would you take a moment to leave us a review? [review_link]";
                    } else {
                        if ($autoNoteGoogleReview->is_set_qualified_response_threshold) {
                            $ask_message = $autoNoteGoogleReview->qualified_review_request_msg_body;
                        } else {
                            $ask_message = $autoNoteGoogleReview->review_request_text_body;
                        }
                    }

                    $tenant_name = !empty($tenant_details->tenant_law_firm_name) ? $tenant_details->tenant_law_firm_name : $tenant_details->tenant_name;

                    $variable_service = new VariableService();
                    $additional_data = [
                        'client_firstname' => $client_firstname,
                        'law_firm_name' => $tenant_name,
                        'review_link' => $review_link,
                        'fv_project_id' => $project_id,
                        'tenant_id' => $tenant_details->id
                    ];
                    $ask_message = $variable_service->updateVariables($ask_message, 'is_review_request_sms', $additional_data);

                    // Find client language and translate
                    $language = LanguageLog::where('tenant_id', $tenant_details->id)->where('fv_client_id', $clientId)->orderBy('updated_at', 'DESC')->first();
                    $latest_language = 'en';
                    if ($language != null) {
                        $latest_language = $language->language;
                    }
                    $tr = new GoogleTranslate();
                    $tr->setSource();
                    $tr->setTarget($latest_language);
                    $ask_message = $tr->translate($ask_message);
                    $custom_message = !empty($custom_message) ? $tr->translate($custom_message) : $custom_message;

                    $twilio_api = new TwilioService();

                    // send the custom message of the phase change and google review request
                    if (count($contact_phones) > 0) {
                        $sent_phone_numbers = [];
                        $config = DB::table('config')->where('tenant_id', $tenant_details->id)->first();
                        $contact_phones = array_reverse($contact_phones);
                        foreach ($contact_phones as $key => $phone) {
                            $to_number = isset($phone['rawNumber']) ? $phone['rawNumber'] : null;
                            if ($to_number == null || in_array($to_number, $sent_phone_numbers)) {
                                continue;
                            }

                            if ($config->default_sms_way_status) {
                                if ($config->default_sms_way == 'labeled_number') {
                                    if (!isset($phone['label']) || empty($phone['label'])) {
                                        continue;
                                    }
                                } else  if ($config->default_sms_way == 'broadcast_number') {
                                    $default_sms_custom_contact_labels = explode(',', $config->default_sms_custom_contact_label);
                                    if (!isset($phone['label']) || empty($phone['label']) || !in_array($phone['label'], $default_sms_custom_contact_labels)) {
                                        continue;
                                    }
                                } else {
                                    if (count($sent_phone_numbers) == 1) {
                                        continue;
                                    }
                                }
                            }

                            $sent_phone_numbers[] = $client_to_number = $to_number;

                            // send custom message of the phase chagne.
                            if (isset($auto_note_occurrences->is_on) && $auto_note_occurrences->is_on == 1 && $custom_message != null) {

                                if ($auto_note_occurrences->is_live == 0) {
                                    $TenantLive = TenantLive::where('tenant_id', $tenant_details->id)->first();
                                    if ($TenantLive != null) {
                                        $to_number = $TenantLive->test_tfa_number;
                                    } else {
                                        $to_number = "";
                                    }
                                }

                                if (!empty($to_number)) {
                                    $array = [
                                        'tenant_id' => $tenant_details->id,
                                        'client_id' => $fv_client->id,
                                        'message_body' => $custom_message,
                                        'from_number' => $twi_sms_number_from,
                                        'msg_type' => 'out',
                                        'type_of_line' => 'PhaseChange',
                                        'to_number' => $to_number,
                                        'project_id' => $project_id
                                    ];
                                    $addMessage = AutoNoteGoogleReviewReplyMessages::create($array);

                                    $smsTimezoneService = new SMSTimezoneHandleService();
                                    $smsTimezoneService->createSMSJobByTimezone($to_number, $twi_sms_number_from, $custom_message, $tenant_details->id, $addMessage->id);

                                    // sent message log.
                                    $msg_obj = ['Job Created Form SMS Number: ' . $to_number, $custom_message];
                                    Logging::warning(json_encode($msg_obj));
                                }
                            } else {
                                $msg_obj = [
                                    'tenant_id' => $tenant_details->id,
                                    'client_id' => $fv_client->id,
                                    'to_number' => $to_number,
                                    'error' => 'send Phase Change SMS is failed ',
                                    'phase_chagne_auto_note_is_on' => $auto_note_occurrences->is_on,
                                    'phase_chagne_auto_note_is_live' => $auto_note_occurrences->is_live,
                                ];
                                Logging::warning(json_encode($msg_obj));
                            }

                            // send google review request part.
                            $to_number = $client_to_number;

                            if (isset($auto_note_occurrences->google_review_is_on) && $auto_note_occurrences->google_review_is_on == 1) {
                                if ((isset($auto_note_exist->is_send_google_review) && $auto_note_exist->is_send_google_review == 1)) { //|| $phaseName == 'Archived'

                                    if ($auto_note_occurrences->google_review_is_live == 0) {
                                        $TenantLive = TenantLive::where('tenant_id', $tenant_details->id)->first();
                                        if ($TenantLive != null) {
                                            $to_number = $TenantLive->test_tfa_number;
                                        } else {
                                            $to_number = "";
                                        }
                                    }

                                    if (!empty($to_number)) {
                                        // send the asking text to user.
                                        $array = [
                                            'tenant_id' => $tenant_details->id,
                                            'client_id' => $fv_client->id,
                                            'message_body' => $ask_message,
                                            'from_number' => $twi_sms_number_from_google_review_check,
                                            'msg_type' => 'out',
                                            'type_of_line' => 'ReviewRequest',
                                            'is_google_review_filter_msg' => 1,
                                            'to_number' => $to_number,
                                            'project_id' => $project_id
                                        ];
                                        $addMessage = AutoNoteGoogleReviewReplyMessages::create($array);

                                        $smsTimezoneService = new SMSTimezoneHandleService();
                                        $smsTimezoneService->createSMSJobByTimezone($to_number, $twi_sms_number_from_google_review_check, $ask_message, $tenant_details->id, $addMessage->id);

                                        // sent message log.
                                        $msg_obj = ['Job Created Form SMS Number: ' . $to_number, $ask_message];
                                        Logging::warning(json_encode($msg_obj));
                                    }
                                } else {
                                    $msg_obj = [
                                        'tenant_id' => $tenant_details->id,
                                        'client_id' => $fv_client->id,
                                        'to_number' => $to_number,
                                        'error' => 'send google review is failed ',
                                        'is_send_google_review' => isset($auto_note_exist->is_send_google_review) ? $auto_note_exist->is_send_google_review : 0,
                                        'google_review_is_on' => $auto_note_occurrences->google_review_is_on,
                                        'google_review_is_live' => $auto_note_occurrences->google_review_is_live,
                                        'zip_code' => $zip_code
                                    ];
                                    Logging::warning(json_encode($msg_obj));
                                }
                            } else {
                                $msg_obj = [
                                    'tenant_id' => $tenant_details->id,
                                    'client_id' => $fv_client->id,
                                    'to_number' => $to_number,
                                    'error' => 'send google review is failed ',
                                    'google_review_is_on' => $auto_note_occurrences->google_review_is_on,
                                    'google_review_is_live' => $auto_note_occurrences->google_review_is_live,
                                ];
                                Logging::warning(json_encode($msg_obj));
                            }

                            if ($is_new_fv_client == true && $new_fv_client_tbl_id != null) {
                                // add new fv client phones.
                                $fv_client_phone_values = array('client_id' => $new_fv_client_tbl_id, 'client_phone' => $client_to_number, 'created_at' => $current_date, 'updated_at' => $current_date);
                                FvClientPhones::create($fv_client_phone_values);
                            }
                        }
                    } else {

                        // This ELSe part is only to send SMS on test two fa number

                        $to_number = "";

                        if (isset($auto_note_occurrences->is_on) && $auto_note_occurrences->is_on == 1 && $custom_message != null) {

                            if ($auto_note_occurrences->is_live == 0) {
                                $TenantLive = TenantLive::where('tenant_id', $tenant_details->id)->first();
                                if ($TenantLive != null) {
                                    $to_number = $TenantLive->test_tfa_number;
                                } else {
                                    $to_number = "";
                                }
                            }

                            if (!empty($to_number)) {
                                $array = [
                                    'tenant_id' => $tenant_details->id,
                                    'client_id' => $fv_client->id,
                                    'message_body' => $custom_message,
                                    'from_number' => $twi_sms_number_from,
                                    'msg_type' => 'out',
                                    'type_of_line' => 'PhaseChange',
                                    'to_number' => $to_number,
                                    'project_id' => $project_id
                                ];
                                $addMessage = AutoNoteGoogleReviewReplyMessages::create($array);

                                $smsTimezoneService = new SMSTimezoneHandleService();
                                $smsTimezoneService->createSMSJobByTimezone($to_number, $twi_sms_number_from, $custom_message, $tenant_details->id, $addMessage->id);

                                // sent message log.
                                $msg_obj = ['Job Created Form SMS Number: ' . $to_number, $custom_message];
                                Logging::warning(json_encode($msg_obj));
                            }
                        }


                        // send google review request part.
                        if (isset($auto_note_occurrences->google_review_is_on) && $auto_note_occurrences->google_review_is_on == 1) {
                            if ((isset($auto_note_exist->is_send_google_review) && $auto_note_exist->is_send_google_review == 1)) { //|| $phaseName == 'Archived'

                                if ($auto_note_occurrences->google_review_is_live == 0) {
                                    $TenantLive = TenantLive::where('tenant_id', $tenant_details->id)->first();
                                    if ($TenantLive != null) {
                                        $to_number = $TenantLive->test_tfa_number;
                                    } else {
                                        $to_number = "";
                                    }
                                }

                                if (!empty($to_number)) {
                                    // send the asking text to user.
                                    $array = [
                                        'tenant_id' => $tenant_details->id,
                                        'client_id' => $fv_client->id,
                                        'message_body' => $ask_message,
                                        'from_number' => $twi_sms_number_from_google_review_check,
                                        'msg_type' => 'out',
                                        'type_of_line' => 'ReviewRequest',
                                        'is_google_review_filter_msg' => 1,
                                        'to_number' => $to_number,
                                        'project_id' => $project_id
                                    ];
                                    $addMessage = AutoNoteGoogleReviewReplyMessages::create($array);

                                    $smsTimezoneService = new SMSTimezoneHandleService();
                                    $smsTimezoneService->createSMSJobByTimezone($to_number, $twi_sms_number_from_google_review_check, $ask_message, $tenant_details->id, $addMessage->id);

                                    // sent message log.
                                    $msg_obj = ['Job Created Form SMS Number: ' . $to_number, $ask_message];
                                    Logging::warning(json_encode($msg_obj));
                                }
                            } else {
                                $msg_obj = [
                                    'tenant_id' => $tenant_details->id,
                                    'client_id' => $fv_client->id,
                                    'to_number' => $to_number,
                                    'error' => 'send google review is failed ',
                                    'is_send_google_review' => isset($auto_note_exist->is_send_google_review) ? $auto_note_exist->is_send_google_review : 0,
                                    'google_review_is_on' => $auto_note_occurrences->google_review_is_on,
                                    'google_review_is_live' => $auto_note_occurrences->google_review_is_live,
                                    'zip_code' => $zip_code
                                ];
                                Logging::warning(json_encode($msg_obj));
                            }
                        }
                    }
                }
            }

            // END Messaging


            $is_exist = WebhookSettings::where([
                'phase_change_event' => $wh->phase_change_event,
                'tenant_id' => $wh->tenant_id,
                'trigger_action_name' => 'PhaseChanged',
                'is_active' => 1
            ])->get();

            if (count($is_exist) > 0) {
                foreach ($is_exist as $ws) {
                    $phaseChangeType = $ws->item_change_type;
                    $phaseChangeEvent = $ws->phase_change_event;
                    if (strtolower($phaseChangeEvent) == strtolower($wh->phase_change_event)) {
                        $url = $ws->delivery_hook_url;
                        $ch = curl_init($url);
                        $data = json_encode($data_array);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $result = curl_exec($ch);
                        curl_close($ch);
                        $log = array("wh" => $ws, "webhook_hit" => true);
                        Logging::warning(json_encode($log));
                    }
                }

                $this->wh->update([
                    'is_handled' => 1,
                ]);
            } else {
                //            return "Webhook Setting Invalid!";
            }
        } catch (Exception $e) {
            Logging::warning(json_encode($e));
            return $e->getMessage();
        }
    }

    /**
     * [GET] Private Function To Get Custom Message
     */
    private function generateCustomMessage($tenant_details, $custom_message, $client_name, $domain, $project_id = null)
    {
        $response = $custom_message;
        $tenant_name = !empty($tenant_details->tenant_law_firm_name) ? $tenant_details->tenant_law_firm_name : $tenant_details->tenant_name;

        $variable_service = new VariableService();
        $additional_data = [
            'client_firstname' => $client_name,
            'law_firm_name' => $tenant_name,
            'client_portal_url' => route('client', ['subdomain' => $domain]),
            'fv_project_id' => $project_id,
            'tenant_id' => $tenant_details->id

        ];
        $response = $variable_service->updateVariables($response, 'is_phase_change_sms', $additional_data);

        return $response;
    }
}
