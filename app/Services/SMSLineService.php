<?php

namespace App\Services;

use Illuminate\Support\Facades\Log as Logging;
use App\Services\FilevineService;
use App\Services\TwilioService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Models\SmsLineConfig;
use App\Models\Tenant;
use App\Models\AutoNoteGoogleReviewReplyMessages;
use App\Models\MassMessage;
use App\Models\MassMessageLog;
use App\Models\FvClientPhones;
use App\Models\FvClients;

class SMSLineService
{

    protected $_tenant_id;

    /**
     * SMS Line Response Service Constructor
     */
    public function __construct($params)
    {
        try {
            $tenant_id = $this->_tenant_id = $params['tenant_id'];
            $sms_line = $params['sms_line'];
            $sms_line_config = SmsLineConfig::where('tenant_id', $tenant_id)->where($sms_line, true)->first();
            if ($sms_line_config != null) {
                $this->postActivity($params, $sms_line_config);
            }
        } catch (\Exception $ex) {
            $error = [
                __FILE__,
                __LINE__,
                $ex->getMessage()
            ];
            Logging::warning(json_encode($error));
        }
    }

    public function postActivity($params, $sms_line_config)
    {
        $sms_line = $params['sms_line'];
        $params['sms_response_text'] = 'This SMS line is unmonitored. To speak with your Legal Team, please log into the Client Portal.';
        $params['default_org_mailroom_number']  = $sms_line_config->default_org_mailroom_number;

        if ($sms_line == 'post_phase_change_response') {
            $params['type_of_line'] = 'PhaseChange';
            if ($sms_line_config->phase_change_response) {
                $params['sms_response_text'] = $sms_line_config->phase_change_response_text;
            }
            $params['from_number'] = config('twilio.twilio.connections.twilio.phase_change');
        } else if ($sms_line == 'post_review_request_response') {
            $params['type_of_line'] = 'ReviewRequest';
            if ($sms_line_config->review_request_response) {
                $params['sms_response_text'] = $sms_line_config->review_request_response_text;
            }
            $params['from_number'] = config('twilio.twilio.connections.twilio.review_request');
        } else if ($sms_line == 'post_mass_text_response') {
            $params['type_of_line'] = 'MassMessage';
            if ($sms_line_config->mass_text_response) {
                $params['sms_response_text'] = $sms_line_config->mass_text_response_text;
            }
            $params['from_number'] = config('twilio.twilio.connections.twilio.mass_message');
        }


        // Check if the SMS come from project sms number or mailroom number, then reply to client
        $order_of_operation = true;
        if (isset($params['message_note']) && ($params['message_note'] == 'sendToProjectSMSNumber' || $params['message_note'] == 'sendToMailroom')) {
            $order_of_operation = false;
            $client_phone_number = "";
            if ($sms_line == 'post_mass_text_response') {
                $is_exist = DB::table('mass_messages')
                    ->join('mass_message_logs', 'mass_messages.id', '=', 'mass_message_logs.mass_message_id')
                    ->select('mass_message_logs.person_number as client_phone')
                    ->where('mass_messages.tenant_id', $this->_tenant_id)
                    ->whereNull('mass_message_logs.note')
                    ->orderBy('mass_message_logs.created_at', 'DESC')->first();
            } else {
                $is_exist = FvClientPhones::select('client_phone')->where('client_id', $params['client_id'])->first();
                if (!isset($is_exist->client_phone)) {
                    $is_exist = AutoNoteGoogleReviewReplyMessages::select('to_number as client_phone')->where('tenant_id', $this->_tenant_id)->whereNull('note')->whereNull('is_replied')->latest()->first();
                }
            }
            if (isset($is_exist->client_phone)) {
                $client_phone_number = $is_exist->client_phone;
            }
            $params['message_body_from_number'] = $client_phone_number;
            $params['sms_response_text'] = $params['message_body'];
        }


        // Client SMS reply As per SMS response configuration text. Don't send client SMS reply when review link.
        $stop_client_reply = (isset($params['stop_client_reply']) && $params['stop_client_reply']) ? true : false;
        if (!$stop_client_reply) {
            $this->clientSMSReply($params);
        }

        // Handle client response text
        if ($order_of_operation) {
            $sms_line_config_post_orders[$sms_line_config->project_sms_number_order] = 'sendToProjectSMSNumber';
            $sms_line_config_post_orders[$sms_line_config->mailroom_order] = 'sendToMailroom';
            $sms_line_config_post_orders[$sms_line_config->project_feed_note_order] = 'postToFilevineProjectFeedAsNote';
            ksort($sms_line_config_post_orders);

            $fun1 = $sms_line_config_post_orders[1];
            $fun2 = $sms_line_config_post_orders[2];
            $fun3 = $sms_line_config_post_orders[3];

            $status = $this->$fun1($params);
            if (!$status) {
                $status = $this->$fun2($params);
                if (!$status) {
                    $status = $this->$fun3($params);
                }
            }
        }
    }


    public function clientSMSReply($params)
    {
        $this->sendSMS($params, $params['message_body_from_number'], $params['from_number'], $params['sms_response_text']);
        return true;
    }


    public function sendToProjectSMSNumber($params)
    {
        $tenant_id = $params['tenant_id'];
        $tenant_details = Tenant::where('id', $tenant_id)->first();
        $apiurl = config('services.fv.default_api_base_url');
        if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
            $apiurl = $tenant_details->fv_api_base_url;
        }
        $filevine_api = new FilevineService($apiurl, "", $tenant_id);
        $sms_line = $params['sms_line'];
        if ($sms_line == 'post_mass_text_response') {
            $project_ids = $this->getProjectIdByContact($params['person_number']);
        } else {
            $project_ids[] = $params['project_id'];
        }
        $ret = false;
        foreach ($project_ids as $project_id) {
            $project_vitals = json_decode($filevine_api->getProjectsVitalsById($project_id));
            $project_vital_sms_number = "";
            foreach ($project_vitals as $project_vital) {
                if ($project_vital->fieldName == 'projectSmsNumber') {
                    $project_vital_sms_number = isset($project_vital->value) ? $project_vital->value : '';
                }
            }
            if (!empty($project_vital_sms_number)) {
                $params['note'] = 'sendToProjectSMSNumber';
                $this->sendSMS($params, $project_vital_sms_number, $params['from_number'], $params['message_body']);
                $ret = true;
            }
        }
        return $ret;
    }

    public function sendToMailroom($params)
    {
        if (!empty($params['default_org_mailroom_number'])) {
            $params['note'] = 'sendToMailroom';
            $this->sendSMS($params, $params['default_org_mailroom_number'], $params['from_number'], $this->formatMessage($params));
            return true;
        }
        return false;
    }

    public function postToFilevineProjectFeedAsNote($params)
    {
        $tenant_id = $params['tenant_id'];
        $tenant_details = Tenant::where('id', $tenant_id)->first();
        $apiurl = config('services.fv.default_api_base_url');
        if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
            $apiurl = $tenant_details->fv_api_base_url;
        }
        $filevine_api = new FilevineService($apiurl, "", $tenant_id);

        $sms_line = $params['sms_line'];
        if ($sms_line == 'post_mass_text_response') {
            $project_ids = $this->getProjectIdByContact($params['person_number']);
        } else {
            $project_ids[] = $params['project_id'];
        }

        $ret = false;
        foreach ($project_ids as $project_id) {
            $fv_params = [
                'projectId' => ['native' => $project_id, 'partner' => null],
                'body' => $this->formatMessage($params),
            ];
            $res = $filevine_api->createNote($fv_params);
            if (!empty($res)) {
                $msg_obj = ['Created Note By SMS Line Service: ' . $project_id, $params['message_body']];
                Logging::warning(json_encode($msg_obj));
                $ret = true;
            }
        }
        return $ret;
    }

    public function sendSMS($params, $to_number, $from_number, $msg_content)
    {

        if (env('APP_ENV') == 'production') {
            $twilio_api = new TwilioService();
            $msgresponse = $twilio_api->send_sms_message($to_number, $from_number, $msg_content);
            if (isset($msgresponse['sid']) and !empty($msgresponse['sid'])) {
                $msg_obj = ['Sent SMS By SMS Line Service: ' . $to_number, $msg_content];
                Logging::warning(json_encode($msg_obj));

                $client_phone_fv_format = $this->_normilizeToFVPhone($to_number);

                // Store message log on table
                $type_of_line = isset($params['type_of_line']) ? $params['type_of_line'] : '';
                if ($type_of_line == 'MassMessage') {
                    $MassMessage = MassMessage::create([
                        'tenant_id' => isset($params['tenant_id']) ? $params['tenant_id'] : 0,
                        'message_body' => $msg_content
                    ]);
                    MassMessageLog::create([
                        'mass_message_id' => $MassMessage->id,
                        'person_name' => $to_number,
                        'person_number' => $client_phone_fv_format,
                        'from_number' => $from_number,
                        'is_inbound' => 0,
                        'is_sent' => true,
                        'note' => isset($params['note']) ? $params['note'] : null,
                        'sent_at' => Carbon::now()
                    ]);
                } else {
                    $array = [
                        'tenant_id' => isset($params['tenant_id']) ? $params['tenant_id'] : 0,
                        'project_id' => isset($params['project_id']) ? $params['project_id'] : 0,
                        'client_id' => isset($params['client_id']) ? $params['client_id'] : 0,
                        'message_id' => isset($msgresponse['sid']) ? $msgresponse['sid'] : '',
                        'message_body' => $msg_content,
                        'from_number' => $from_number,
                        'msg_type' => 'out',
                        'type_of_line' => $type_of_line,
                        'is_google_review_filter_msg' => ($type_of_line == 'ReviewRequest') ? 1 : 0,
                        'to_number' => $client_phone_fv_format,
                        'note' => isset($params['note']) ? $params['note'] : null,
                    ];
                    AutoNoteGoogleReviewReplyMessages::create($array);
                }
            }
        }
    }

    public function getProjectIdByContact($person_number)
    {
        $tenant_details = Tenant::where('id', $this->_tenant_id)->first();
        $apiurl = config('services.fv.default_api_base_url');
        if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
            $apiurl = $tenant_details->fv_api_base_url;
        }
        $filevine_api = new FilevineService($apiurl, "", $this->_tenant_id);
        $contacts = json_decode($filevine_api->getContacts(['phone' => $person_number]));
        $project_links = [];
        if (isset($contacts->items)) {
            foreach ($contacts->items as $item) {
                if (isset($item->links->projects)) {
                    $project_links[] = $item->links->projects;
                }
            }
        }
        $project_ids = [];
        if (count($project_links)) {
            foreach ($project_links as $project_link) {
                $projects = json_decode($filevine_api->getProjectByContactProjectLink($project_link));
                if (isset($projects->items)) {
                    foreach ($projects->items as $item) {
                        if (isset($item->projectId->native)) {
                            $project_ids[$item->projectId->native] = $item->projectId->native;
                        }
                    }
                }
            }
        }
        return $project_ids;
    }

    /**
     * Convert the phone number format for FV
     * ex:  "+15017122661" =>  "5017122661"
     */
    public function _normilizeToFVPhone($phone)
    {
        $phoneNorm = str_replace(array(' ', '-', '(', ')', '+',), '', $phone);
        if (strlen($phoneNorm) == 12 && substr($phoneNorm, 0, 2) == '+1') {
            $phoneNorm = substr($phoneNorm, 2, 10);
        }
        if (strlen($phoneNorm) == 11 && substr($phoneNorm, 0, 1) == 1) {
            $phoneNorm = substr($phoneNorm, 1, 10);
        }
        return $phoneNorm;
    }


    public function formatMessage($params)
    {
        $fv_note_body = $params['message_body'];
        $client_info = isset($params['message_body_from_number']) ? $params['message_body_from_number'] : '';
        $client_id = isset($params['client_id']) ? $params['client_id'] : 0;
        if (!empty($client_id)) {
            $client = FvClients::find($client_id);
            if ($client != null) {
                $client_info .=  ' (' . $client->fv_client_name . ')';
            }
        }

        $type_of_line = isset($params['type_of_line']) ? $params['type_of_line'] : '';
        $type_of_line = preg_replace('/[A-Z]/', ' $0', $type_of_line);
        $fv_note_body .= ' [From ' . $client_info . ' on VineConnect ' . $type_of_line . ' SMS Reply Line]';
        return $fv_note_body;
    }
}
