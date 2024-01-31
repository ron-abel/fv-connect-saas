<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log as Logging;

use App\Models\Tenant;
use Exception;
use App\Models\MediaLocker;


class TwilioService
{
    protected $_baseUrl_twilio;
    protected $_baseUrl_twilio_sms;
    protected $_Twilio_Account_SID;
    protected $_Twilio_Auth_Token;
    protected $_Twilio_Auth_Sid;

    public function __construct($base_url_twilio = 'https://verify.twilio.com/v2/', $baseUrl_twilio_sms = 'https://api.twilio.com/2010-04-01/Accounts/')
    {

        $this->created_verification_service($base_url_twilio, $baseUrl_twilio_sms);
    }

    function created_verification_service($base_url_twilio, $baseUrl_twilio_sms)
    {

        $this->_baseUrl_twilio = $base_url_twilio;
        $this->_baseUrl_twilio_sms = $baseUrl_twilio_sms;
        $api_url = $baseUrl_twilio_sms . "/Services";

        try {

            $Twilio_Account_SID = config('twilio.twilio.connections.twilio.account_sid');
            $this->_Twilio_Account_SID = $Twilio_Account_SID;

            $Twilio_Auth_Token = config('twilio.twilio.connections.twilio.token');
            $this->_Twilio_Auth_Token = $Twilio_Auth_Token;
            $Twilio_Auth_Sid = config('twilio.twilio.connections.twilio.sid');
            $this->_Twilio_Auth_Sid = $Twilio_Auth_Sid;

        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    public function verify_twilio_api()
    {
        if ($this->_Twilio_Account_SID) {
            return true;
        } else {
            return false;
        }
    }

    function send_verification_token($to, $channel)
    {
        try {
            $api_url = $this->_baseUrl_twilio . "/Services/" . $this->_Twilio_Account_SID . "/Verifications";

            $to = urlencode($to);

            $params = "To=" . $to . "&Channel=" . $channel;

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "POST",
                CURLOPT_POSTFIELDS      => $params,
                CURLOPT_HTTPHEADER => array("Content-Type: application/x-www-form-urlencoded", "Authorization: Basic " . base64_encode($this->_Twilio_Auth_Sid . ":" . $this->_Twilio_Auth_Token)),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                $response = json_decode($response, TRUE);
                return $response;
            }
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    function verify_verification_token($to, $code, $service_sid)
    {
        try {
            $api_url = $this->_baseUrl_twilio . "/Services/" . $service_sid . "/VerificationCheck";

            $to = urlencode($to);

            $params = "To=" . $to . "&Code=" . $code;

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "POST",
                CURLOPT_POSTFIELDS      => $params,
                CURLOPT_HTTPHEADER       => array("Content-Type: application/x-www-form-urlencoded", "Authorization: Basic " . base64_encode($this->_Twilio_Auth_Sid . ":" . $this->_Twilio_Auth_Token)),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                $response = json_decode($response, TRUE);
                return $response;
            }
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    public function send_sms_message($to, $from, $body)
    {
        try {
            $api_url = $this->_baseUrl_twilio_sms . $this->_Twilio_Account_SID . "/Messages.json";

            $to = urlencode($to);
            if (env('APP_ENV') == 'local') {
                $to = config('services.sms.test-target-number');
            }
            // get media urls
            $parsed_body = $this->getMediaUrls($body);
            $body = urlencode($parsed_body['body']);
            $from = urlencode($from);

            $params = "Body=" . $body . "&From=" . $from . "&To=" . $to;

            // check if there are any media objects exist
            if(count($parsed_body['media']) > 0) {
                foreach($parsed_body['media'] as $key => $value) {
                    $params .= "&MediaUrl=" . urlencode($value);
                }
            }

            $msg_obj = ['Sent SMS By Twilio =>  ' .$params];
            Logging::warning(json_encode($msg_obj));

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "POST",
                CURLOPT_POSTFIELDS      => $params,
                CURLOPT_HTTPHEADER       => array("Content-Type: application/x-www-form-urlencoded", "Authorization: Basic " . base64_encode($this->_Twilio_Auth_Sid . ":" . $this->_Twilio_Auth_Token)),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                $response = json_decode($response, TRUE);
                return $response;
            }
        } catch (\Exception $e) {
            $msg_obj = ['SMS Failed =>  ' .$e->getMessage()];
            Logging::warning(json_encode($msg_obj));

            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    private function getMediaUrls($body) {
        $response = [
            'body' => $body,
            'media' => []
        ];
        try {
            $matches = [];
            preg_match_all('/<MEDIA>(.*?)<\/MEDIA>/', $body, $matches);
            if(isset($matches[1]) && count($matches[1]) > 0) {
                $matches_array = $matches[1];
                for ($i=0; $i < count($matches_array); $i++) { 
                    $media_locker = MediaLocker::where('media_code', $matches_array[$i])->first();
                    if(isset($media_locker->id)) {
                        $response['media'][] = $media_locker->media_url;
                    }
                }
            }
            $response['body'] = preg_replace('/<MEDIA>(.*?)<\/MEDIA>/', '', $body);
        }
        catch(\Exception $ex) {
            $error = [
                __FILE__,
                __LINE__,
                $ex->getMessage()
            ];
            Logging::warning(json_encode($error));
        }
        return $response;
    }
}
