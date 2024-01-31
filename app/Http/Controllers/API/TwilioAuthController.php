<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as Logging;
use Illuminate\Support\Facades\DB;
use App\Models\API_LOG;
use App\Models\TWOFA_VERIFICATIONS;
use App\Services\TwilioService;
use App\Virtual\APIResponse;
use Twilio;
use Stichoza\GoogleTranslate\GoogleTranslate;
use App\Models\TenantNotificationConfig;
use App\Services\NotificationHandlerService;
use App\Models\Tenant;

class TwilioAuthController extends Controller
{

    protected  $response;


    public function __construct(APIResponse $response)
    {
        $this->response = $response;
        Controller::setSubDomainName();
    }

    /**
     * @OA\Post(
     *      path="/api/v1/twilio/send2faAuthCode",
     *      description="Send verification code to mobile number",
     *      operationId="auth",
     *      tags={"Twilio Verification"},
     *      summary="Send Verification Code",
     *      description="Send and verify verification through twilo",
     * @OA\RequestBody(
     *    required=true,
     *    description="",
     *    @OA\JsonContent(
     *       required={"api_token","to_number", "request_domain","fv_project_id","user_ip"},
     *       @OA\Property(property="api_token", type="string", example="xxxx"),
     *       @OA\Property(property="request_domain", type="string",  example="test.com"),
     *       @OA\Property(property="to_number", type="string",  example="+1813463232,+1813463231,..."),
     *       @OA\Property(property="fv_project_id", type="string",  example="00000"),
     *       @OA\Property(property="user_ip", type="string",  example="000.000.000.0"),
     *       @OA\Property(property="tenant_id", type="string",  example="1"),
     *    ),
     * ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *          @OA\Property(property="success", type="bool", example="true/false"),
     *          @OA\Property(property="status", type="string", example="success"),
     *          @OA\Property(property="message", type="string",  example="Sent successfully"),
     *          @OA\Property(property="service_sid", type="string",  example="xxxxxxxxx"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function send2faAuthCode(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'api_token'       => 'required',
                'to_number'       => 'required',
                'request_domain'  => 'required',
                'fv_project_id'   => 'required',
                'user_ip'         => 'required',
            ]);


            if ($validator->fails()) {
                return $this->response->validation($validator->errors());
            }

            $valide = $this->validateLimit($request->fv_project_id);
            if (isset($valide) && $valide == false) {
                return $this->response->response_error("Too many API Calls");
            }
            if ($request->api_token == config('app.api_custom_token')) {
                $test_sms_number_from = env('TWILIO_FROM');
                $twilio_api = new TwilioService();

                // check multiple to_number.
                $verification_code = "";
                $to_number_str = $request->to_number;
                $to_numbers = explode(",", $request->to_number);

                if (count($to_numbers) > 0) {

                    $code = rand(10000, 99999);
                    $service_sid = md5($code + strtotime(date('Y-m-d H:i:s')));

                    foreach ($to_numbers as $key => $to_number) {
                        $sms_number = $to_number;
                        $code_key = $code . $key;
                        $verification_code .= $code_key . ",";

                        $tr = new GoogleTranslate();
                        $tr->setSource();
                        $tr->setTarget((isset($request->language) ? $request->language : 'en'));

                        // Get Tenant Law Firm Name
                        $tenant_law_firm_name = "";
                        if(isset($request->tenant_id) && $request->tenant_id){
                            $tenant = Tenant::find($request->tenant_id);
                            if($tenant != null){
                                $tenant_law_firm_name = $tenant->tenant_law_firm_name;
                            }
                        }

                        $body = $tr->translate("Your") . ' ';
                        $body .= !empty($tenant_law_firm_name) ? $tenant_law_firm_name . ' ' : '';
                        $body .= $tr->translate("Client Portal login verification code is:") . ' '  . $code_key;

                        $response = null;
                        if (env('APP_ENV') == 'production') {
                            $response = $twilio_api->send_sms_message($sms_number, $test_sms_number_from, $body);
                        }

                        if (isset($response['sid']) || env('APP_ENV') == 'local') {
                            $twoFA = TWOFA_VERIFICATIONS::create([
                                'service_sid'     => $service_sid,
                                'phone'           => $sms_number,
                                'code'            => $code_key
                            ]);
                        }
                    }
                } else {
                    return $this->response->response_error("Invalid To numbers!");
                }

                API_LOG::create([
                    'ip'              => request()->ip(),
                    'request_domain'  => $request->request_domain,
                    'api_name'        => 'send2faAuthCode',
                    'to_number'       => $to_number_str,
                    'fv_project_id'   => $request->fv_project_id,
                    'verification_code' => rtrim($verification_code, ','),
                    'user_ip'         => $request->user_ip,
                    'tenant_id'       => isset($request->tenant_id) ? $request->tenant_id : "",
                ]);

                return $this->response->response_success($service_sid);
            } else {
                return $this->response->invalid();
            }
        } catch (\Exception $ex) {
            Logging::warning($ex->getMessage());
        }
    }

    /**
     * @OA\Post(
     *      path="/api/v1/twilio/validate2faAuthCode",
     *      description="Verify Verification code",
     *      operationId="auth1",
     *      tags={"Twilio Verification"},
     *      summary="Verifiy Code sent to mobile",
     *      description="Send and verify verification through twilo",
     * @OA\RequestBody(
     *    required=true,
     *    description="",
     *    @OA\JsonContent(
     *       required={"api_token", "request_domain", "verification_code", "service_sids","fv_project_id","user_ip"},
     *       @OA\Property(property="api_token", type="string", example="xxxx"),
     *       @OA\Property(property="request_domain", type="string",  example="test.com"),
     *       @OA\Property(property="verification_code", type="string",  example="xxxxx"),
     *       @OA\Property(property="service_sid", type="string",  example="xxxxxxx"),
     *       @OA\Property(property="fv_project_id", type="string",  example="00000"),
     *       @OA\Property(property="user_ip", type="string",  example="000.000.000.0"),
     *       @OA\Property(property="tenant_id", type="string",  example="1"),
     *    ),
     * ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *          @OA\Property(property="success", type="bool", example="true/false"),
     *          @OA\Property(property="status", type="string", example="success"),
     *          @OA\Property(property="message", type="string",  example="Sent successfully"),
     *          @OA\Property(property="verified_contact_number", type="string", example="+18132324324")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )`
     */
    public function validate2faAuthCode(Request $request)
    {
        try {
            $test_sms_number_from = env('TWILIO_FROM');

            $validator = \Validator::make($request->all(), [
                'api_token'          => 'required',
                'request_domain'     => 'required',
                'verification_code'  => 'required',
                'service_sid'        => 'required',
                'fv_project_id'      => 'required',
                'user_ip'            => 'required',
            ]);

            if ($validator->fails()) {
                return $this->response->validation($validator->errors());
            }

            $valide = $this->validateLimit($request->fv_project_id);
            if (isset($valide['message']) && $valide['message'] == false) {
                return $this->response->response_error("Too many API Calls");
            }

            if ($request->api_token == config('app.api_custom_token')) {

                $service_sid = $request->service_sid;

                $twilio_api = new TwilioService();

                $verified_contact_number = "";

                // leave the API log
                $api_log = API_LOG::create([
                    'ip'              => request()->ip(),
                    'request_domain'  => $request->request_domain,
                    'api_name'        => 'validate2faAuthCode',
                    'to_number'       => $verified_contact_number,
                    'fv_project_id'   => $request->fv_project_id,
                    'user_ip'         => $request->user_ip,
                    'verification_code' => $request->verification_code,
                    'tenant_id'       => $request->tenant_id,
                ]);

                if ($twilio_api->verify_twilio_api()) {
                    $is_valid_code = 0;

                    $verification_code = $request->verification_code;

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
                            } else {
                                TWOFA_VERIFICATIONS::where(['service_sid' => $service_sid, 'id' => $matched_record['id']])
                                    ->update([
                                        'tries' => $matched_record->tries + 1,
                                    ]);
                                return $this->response->response_error('Verification Code is expired.');
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
                            return $this->response->response_error('Invalid Code. Try again Later!');
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
                        $notification_config = TenantNotificationConfig::where('tenant_id', $request->tenant_id)->where('event_short_code', TenantNotificationConfig::UnsuccessfulLogin)->first();
                        if ($notification_config) {
                            $params = [
                                'project_id' => $request->fv_project_id,
                                'tenant_id' => $request->tenant_id,
                                'client_id' => !is_null(session()->get('contact_id')) ? session()->get('contact_id') : 0,
                                'note_body' => '2+ Unsuccessful Client Portal Login Attempts',
                                'action_name' => '2+ Unsuccessful Client Portal Login Attempts',
                            ];
                            NotificationHandlerService::callActionService($notification_config, $params);
                        }

                        return $this->response->response_error('Tried too many times.');
                    }


                    if ($verified_contact_number != "" && $api_log->id) {
                        // update api_log table field.
                        $api_log->to_number = $verified_contact_number;
                        $api_log->save();
                    }



                    if ($is_valid_code == 0) {
                        return $this->response->response_error('Invalid Code. Try again Later!');
                    } else {
                        // validated.
                        return $this->response->validate_response_success($verified_contact_number);
                    }
                } else {
                    return $this->response->response_error('Invalid Twillo');
                }
            } else {
                return $this->response->invalid();
            }
        } catch (\Exception $ex) {
            Logging::warning($ex->getMessage());
        }
    }

    // validate API Call Limitatin.
    private function validateLimit($projectID)
    {

        $time_range = (int)env('MAX_TWI_API_DURATION') / 60;
        $count = DB::select(DB::raw("Select * from api_logs where created_at > DATE_SUB(NOW(), INTERVAL " . $time_range . " HOUR) AND fv_project_id = '" . $projectID . "'"));
        if (count($count) > (int)env('MAX_TWI_API_CALL')) {
            return $result['message'] = false;
        } else {
            return $result['message'] = true;
        }
    }
}
