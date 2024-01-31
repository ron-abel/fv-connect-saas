<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as Logging;
use Illuminate\Support\Facades\Session;
use App\Services\SendGridServices;
use Stichoza\GoogleTranslate\GoogleTranslate;
use App\Models\TenantNotificationConfig;
use App\Services\NotificationHandlerService;

use App\Models\Tenant;
use App\Models\TenantLive;
use App\Models\Log;
use App\Services\TwilioService;
use App\Services\FilevineService;
use App\Models\API_LOG;
use App\Models\TWOFA_VERIFICATIONS;
use App\Models\LanguageLog;
use Exception;
use GrahamCampbell\ResultType\Success;
use Illuminate\Support\Facades\DB;

class VerifyClientController extends Controller
{

    public $cur_tenant_id;
    private $sendGridServices;

    public function __construct()
    {
        Controller::setSubDomainName();
        $this->cur_tenant_id = session()->get('tenant_id');
        $this->sendGridServices = new SendGridServices($this->cur_tenant_id);
    }

    /**
     * GET : Twilio Integration by API
     */
    public function send2faAuthCode(Request $request, $domain, $project_id, $phone_no)
    {
        try {
            $TenantLive = TenantLive::where('tenant_id', $this->cur_tenant_id)
                ->first();
            if (isset($TenantLive, $TenantLive->status) && !empty($TenantLive) && $TenantLive->status !== 'live') {
                if (isset($TenantLive->test_tfa_number) && $TenantLive->test_tfa_number != null) {
                    $phone_no = $TenantLive->test_tfa_number;
                } else {
                    // error test 2FA number is invalid.
                    return redirect()->back()->with('message', "Invalid 2FA Number! Please ask to the support team!");
                }
            }

            // Save data into fv_client_language_logs
            $languageMatch = [
                'tenant_id' => $this->cur_tenant_id,
                'fv_client_id' => !is_null(session()->get('contact_id')) ? session()->get('contact_id') : 0,
                'client_ip' => $request->getClientIp(),
                'language' => !is_null(session()->get('lang')) ? session()->get('lang') : 'en'
            ];
            $languageData = $languageMatch;
            $languageData['updated_at'] = date('Y-m-d H:i:s');
            LanguageLog::updateOrCreate($languageMatch, $languageData);

            // redirect part.
            if (session()->has('message') || session()->has('service_sid')) {
                return $this->_loadContent('client.pages.2fa_verify', [
                    'projectID' => $project_id,
                    'service_sid' => session()->get('service_sid')
                ]);
            }


            // send 2fa code part...

            $contact_id =  session()->get('contact_id');
            $config = DB::table('config')->where('tenant_id', $this->cur_tenant_id)->first();
            $sent_phone_numbers = [];

            if ($config->default_sms_way_status && isset($TenantLive, $TenantLive->status) && !empty($TenantLive) && $TenantLive->status == 'live') {
                $tenant_details = Tenant::where('id', $this->cur_tenant_id)->first();
                $apiurl = config('services.fv.default_api_base_url');
                if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
                    $apiurl = $tenant_details->fv_api_base_url;
                }
                $filevine_api = new FilevineService($apiurl, "", $this->cur_tenant_id);
                $contact_details = json_decode($filevine_api->getContactByContactId($contact_id));

                if (!empty($contact_details) && isset($contact_details->phones) && count($contact_details->phones)) {
                    $contact_phones = $contact_details->phones;
                    $contact_phones = array_reverse($contact_phones);
                    foreach ($contact_phones as $phone) {
                        $to_number = isset($phone->rawNumber) ? $phone->rawNumber : null;
                        if ($to_number == null || in_array($to_number, $sent_phone_numbers)) {
                            continue;
                        }
                        if ($config->default_sms_way == 'broadcast_number') {
                            $default_sms_custom_contact_labels = explode(',', $config->default_sms_custom_contact_label);
                            if (!isset($phone->label) || empty($phone->label) || !in_array($phone->label, $default_sms_custom_contact_labels)) {
                                continue;
                            }
                        } else {
                            if (count($sent_phone_numbers) == 1) {
                                continue;
                            }
                        }
                        $sent_phone_numbers[] = $to_number;
                    }
                }
            }

            if (count($sent_phone_numbers) && $config->default_sms_way_status && $config->number_submitted_by_user && !in_array($phone_no, $sent_phone_numbers)) {
                $sent_phone_numbers[] = $phone_no;
            }

            if (!count($sent_phone_numbers)) {
                $sent_phone_numbers[] = $phone_no;
            }

            $match_field = session()->get('match_field');
            $response = null;

            if ($match_field == 'phone_email' || $match_field == 'phone') {
                $api_url = config('app.superadmin') . '.' . config('app.domain') . '/api/v1/twilio/send2faAuthCode';
                if (config('app.env') == 'production') {
                    $api_url = 'https://' . $api_url;
                }
                $params = [
                    'api_token'       => config('app.api_custom_token'),
                    'to_number'       => implode(",", $sent_phone_numbers),
                    'request_domain'  => session()->get('subdomain') . '.' . config('app.domain'),
                    'fv_project_id'   => $project_id,
                    'user_ip'         => request()->ip(),
                    'tenant_id'       => $this->cur_tenant_id,
                    'language'        => !is_null(session()->get('lang')) ? session()->get('lang') : 'en'
                ];
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL             => $api_url,
                    CURLOPT_RETURNTRANSFER  => true,
                    CURLOPT_CUSTOMREQUEST   => "POST",
                    CURLOPT_POSTFIELDS      => json_encode($params),
                    CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Accept: application/json')
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);
                if ($err) {
                    echo "cURL Error #:" . $err;
                } else {
                    $response = json_decode($response, TRUE);
                }
            } else if ($match_field == 'email') {
                $response = $this->send2faEmail();
            }

            if (isset($response['success']) && $response['success']) {
                session()->put('service_sid', $response['service_sid']);
                return $this->_loadContent('client.pages.2fa_verify', [
                    'projectID' => $project_id,
                    'service_sid' => $response['service_sid']
                ]);
            } else {
                Logging::warning($response);
                return redirect()->back()->with('message', $response['message'] ?? "Error in sending 2FA code!");
            }
        } catch (\Exception $ex) {
            $error = [
                __FILE__,
                __LINE__,
                $ex->getMessage()
            ];
            Logging::warning(json_encode($error));
            return view('error');
        }
    }


    /**
     * [POST] Send 2fa Email
     */
    public function send2faEmail()
    {
        try {
            $code = rand(10000, 99999);
            $code_key = $code . '3';
            $service_sid = md5($code + strtotime(date('Y-m-d H:i:s')));

            // Get Tenant Law Firm Name
            $tenant_law_firm_name = "";
            $tenant = Tenant::find($this->cur_tenant_id);
            if ($tenant != null) {
                $tenant_law_firm_name = $tenant->tenant_law_firm_name;
            }

            $response = [];
            if (Session::has('log_id') && !empty(session()->get('log_id'))) {
                $log_id = session()->get('log_id');
                $lookup_email = '';
                $lookup_phone = '';
                $log = Log::where('id', $log_id)->first();
                if ($log != null) {
                    $lookup_email = $log->lookup_email;
                    $lookup_phone = $log->Lookup_Phone_num;
                }

                TWOFA_VERIFICATIONS::create([
                    'service_sid'     => $service_sid,
                    'phone'           => !empty($lookup_phone) ? $lookup_phone : $lookup_email,
                    'code'            => $code_key
                ]);

                if (!empty($lookup_email) || !empty($lookup_phone)) {
                    if (!empty($lookup_email)) {
                        $dynamic_body = [
                            'law_firm_name' => $tenant_law_firm_name,
                            'tfa_code' => $code_key
                        ];
                        $this->sendGridServices->send2faCodeEmail($lookup_email, $dynamic_body);
                    }

                    if (!empty($lookup_phone)) {
                        if (env('APP_ENV') == 'production') {
                            $test_sms_number_from = env('TWILIO_FROM');
                            $twilio_api = new TwilioService();
                            $body = "Your " . $tenant_law_firm_name . " Client Portal login verification code is:" . $code_key;
                            $twilio_api->send_sms_message($lookup_phone, $test_sms_number_from, $body);
                        }
                    }

                    $response = [
                        'success' => true,
                        'service_sid' => $service_sid
                    ];
                }
            }
            return $response;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }


    /**
     * [POST] Verify 2fa Email
     */
    public function verify2faEmail($request)
    {
        try {
            $response = [
                'success' => false,
                'message' => 'Internal error! Try again later!'
            ];

            $is_valid_code = 0;
            $verification_code = $request['verification_code'];
            $service_sid = $request['service_sid'];

            $matched_record = TWOFA_VERIFICATIONS::where(['service_sid' => $service_sid, 'status' => 0, 'code' => $verification_code])->first();
            $record_all = TWOFA_VERIFICATIONS::where(['service_sid' => $service_sid, 'status' => 0])->get();

            $total_verify_tries = 0;
            $record_ids = [];
            foreach ($record_all as $key => $single_response) {
                $total_verify_tries += $single_response->tries;
                $record_ids[] = $single_response->id;
            }

            if ($total_verify_tries < (int)env('MAX_2FA_VERIFY_TRY_NUMBER')) {

                if ($matched_record && isset($matched_record['id'])) {
                    $verified_contact_number = $matched_record->phone;
                    $currentTime  = strtotime(date("Y-m-d H:i:s"));
                    $verifyTime = strtotime($matched_record->created_at);
                    $differenceInSeconds = $currentTime - $verifyTime;

                    if ($differenceInSeconds < (int)env('MAX_2FA_VERIFY_EXPIRE_TIME')) {

                        // verified the code successfully.
                        if (count($record_ids) > 0) {
                            TWOFA_VERIFICATIONS::where(['service_sid' => $service_sid])
                                ->whereIn('id', $record_ids)
                                ->update(['status' => 2]);
                        }

                        TWOFA_VERIFICATIONS::where(['service_sid' => $service_sid, 'id' => $matched_record['id']])
                            ->update([
                                'tries' => $matched_record->tries + 1,
                                'status' => 1,
                            ]);

                        $is_valid_code = 1;
                        $response['success'] = true;
                        $response['verified_contact_number'] = $verified_contact_number;
                    } else {
                        TWOFA_VERIFICATIONS::where(['service_sid' => $service_sid, 'id' => $matched_record['id']])
                            ->update([
                                'tries' => $matched_record->tries + 1,
                            ]);
                        $response['message'] = 'Verification Code is expired.';
                    }
                } else {

                    if (isset($record_all[0], $record_all[0]->id)) {
                        $record_id = $record_all[0]->id;
                        TWOFA_VERIFICATIONS::where('service_sid', $service_sid)
                            ->where('id', $record_id)
                            ->update([
                                'tries' => $record_all[0]->tries + 1
                            ]);
                    }
                    $response['message'] = 'Invalid Code. Try again Later!';
                }
            } else {

                if (isset($record_all[0], $record_all[0]->id)) {
                    $record_id = $record_all[0]->id;
                    TWOFA_VERIFICATIONS::where('service_sid', $service_sid)
                        ->where('id', $record_id)
                        ->update([
                            'tries' => $record_all[0]->tries + 1
                        ]);
                }

                // Send Admin Notification (Email and Post to Filevine)
                $notification_config = TenantNotificationConfig::where('tenant_id', $this->cur_tenant_id)->where('event_short_code', TenantNotificationConfig::UnsuccessfulLogin)->first();
                if ($notification_config) {
                    $params = [
                        'project_id' => $request['fv_project_id'],
                        'tenant_id' => $this->cur_tenant_id,
                        'client_id' => !is_null(session()->get('contact_id')) ? session()->get('contact_id') : 0,
                        'note_body' => '2+ Unsuccessful Client Portal Login Attempts',
                        'action_name' => '2+ Unsuccessful Client Portal Login Attempts',
                    ];
                    NotificationHandlerService::callActionService($notification_config, $params);
                }
                $response['message'] = 'Tried too many times.';
            }

            if ($is_valid_code) {
                $response['message'] = 'Success!';
            }
            return $response;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }


    /**
     * POST: Verfiry Code by Twilio API
     */
    public function validate2faAuthCode(Request $request)
    {

        try {

            $match_field = session()->get('match_field');

            if ($match_field == 'phone_email' || $match_field == 'phone') {
                $api_url = config('app.superadmin') . '.' . config('app.domain') . '/api/v1/twilio/validate2faAuthCode';
                if (config('app.env') == 'production') {
                    $api_url = 'https://' . $api_url;
                }
                $params = [
                    'api_token'       => config('app.api_custom_token'),
                    'verification_code' => $request['verification_code'],
                    'service_sid' => $request['service_sid'],
                    'request_domain'  => session()->get('subdomain') . '.' . config('app.domain'),
                    'fv_project_id'   => $request['fv_project_id'],
                    'user_ip'         => request()->ip(),
                    'tenant_id' => $this->cur_tenant_id
                ];

                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL             => $api_url,
                    CURLOPT_RETURNTRANSFER  => true,
                    CURLOPT_CUSTOMREQUEST   => "POST",
                    CURLOPT_POSTFIELDS      => $params,
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);
                if ($err) {
                    echo "cURL Error #:" . $err;
                } else {
                    $response = json_decode($response, TRUE);
                }
            } else if ($match_field == 'email') {
                $response = $this->verify2faEmail($request->all());
            }

            if (isset($response['success']) && $response['success']) {
                if (Session::has('log_id') && !empty(session()->get('log_id'))) {
                    Log::where('id', session()->get('log_id'))->update([
                        'Result' => 1,
                        'note' => $response
                    ]);
                }
                return redirect()->route('lookup', ['subdomain' => session()->get('subdomain'), 'lookup_project_id' => $request['fv_project_id']]);
            } else {
                Log::where('id', session()->get('log_id'))->update([
                    'note' => !empty($err) ? (json_encode($response) . json_encode($err)) : $response
                ]);
                return redirect()
                    ->back()
                    ->with('message', $response['message'])
                    ->with('service_sid', $request['service_sid']);
            }
        } catch (\Exception $e) {
            $exception_json = json_encode($e);
            Logging::warning($e->getMessage());
            Logging::warning($exception_json);
            return $e->getMessage();
        }
    }


    /**
     * Get the sms numbers from project id
     */
    public function getSmsNumbersFromProjectId($project_id, $request)
    {
        try {
            $tenant_id = $this->cur_tenant_id;
            $Tenant = Tenant::find($tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $filevine_api = new FilevineService($apiurl, "");
            $contact_phones = [];
            $project_obj = json_decode($filevine_api->getProjectsById($project_id), true);
            if (isset($project_obj['clientId']['native'])) {
                $clientId = $project_obj['clientId']['native'];
                $client_obj = json_decode($filevine_api->getContactByContactId($clientId), true);
                if (isset($client_obj['phones']) && count($client_obj['phones']) > 0) {
                    $contact_phones = $client_obj['phones'];
                }
            }

            $sms_numbers = [];
            foreach ($contact_phones as $key => $phone) {
                // find the phone numbers based on the label.
                if (isset($phone['label'], $phone['rawNumber']) && $this->checkIsPersonalPhoneLabel($phone['label']) == true) {
                    $sms_numbers[] = $phone['rawNumber'];
                }
            }

            return $sms_numbers;
        } catch (\Exception $e) {
            $exception_json = json_encode($e);
            Logging::warning($e->getMessage());
            Logging::warning($exception_json);
            return $e->getMessage();
        }
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
            $exception_json = json_encode($e);
            Logging::warning($e->getMessage());
            Logging::warning($exception_json);
            return $e->getMessage();
        }
    }

    /**
     * Resend 2fa to phone or email! send by 1: email, send by 2: phone
     */
    public function reSend2fa(Request $request)
    {
        try {
            $send_by = $request->send_by;
            $service_sid = $request->service_sid;
            $twofa = TWOFA_VERIFICATIONS::where('service_sid', $service_sid)->first();
            if ($twofa == null) {
                return [
                    'success' => false,
                    'message' => "Invalid Service ID"
                ];
            }

            $code_key = $twofa->code;

            // Get Tenant Law Firm Name
            $tenant_law_firm_name = "";
            $tenant = Tenant::find($this->cur_tenant_id);
            if ($tenant != null) {
                $tenant_law_firm_name = $tenant->tenant_law_firm_name;
            }

            $contact_id = !is_null(session()->get('contact_id')) ? session()->get('contact_id') : 0;
            if ($contact_id) {
                $apiurl = config('services.fv.default_api_base_url');
                if (isset($tenant->fv_api_base_url) and !empty($tenant->fv_api_base_url)) {
                    $apiurl = $tenant->fv_api_base_url;
                }
                $filevine_api = new FilevineService($apiurl, "");
                $contact_details = json_decode($filevine_api->getContactByContactId($contact_id));
                if (!empty($contact_details)) {
                    $primaryEmail = $contact_details->primaryEmail;
                    if (!empty($primaryEmail)  && $send_by == "1") {
                        $dynamic_body = [
                            'law_firm_name' => $tenant_law_firm_name,
                            'tfa_code' => $code_key
                        ];
                        $this->sendGridServices->send2faCodeEmail($primaryEmail, $dynamic_body);
                        return [
                            'success' => true,
                            'message' => "Email Sent Succesfully!"
                        ];
                    } else if ($send_by == "2") {
                        $contact_phones = $contact_details->phones;
                        foreach ($contact_phones as $phone) {
                            $to_number = isset($phone->rawNumber) ? $phone->rawNumber : null;
                            if (!empty($to_number)) {
                                if (env('APP_ENV') == 'production') {
                                    $test_sms_number_from = env('TWILIO_FROM');
                                    $twilio_api = new TwilioService();
                                    $body = "Your " . $tenant_law_firm_name . " Client Portal login verification code is:" . $code_key;
                                    $twilio_api->send_sms_message($to_number, $test_sms_number_from, $body);
                                }
                            }
                        }
                        return [
                            'success' => true,
                            'message' => "SMS Sent Succesfully!"
                        ];
                    }
                }
            }
            return [
                'success' => false,
                'message' => "Internal Error!"
            ];
        } catch (\Exception $e) {
            Logging::warning($e->getMessage());
            return $e->getMessage();
        }
    }
}
