<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use App\Models\Tenant;
use Exception;
use Illuminate\Support\Facades\Log as Logging;

class CampaignMonitorService
{
    protected $_baseUrl;
    protected $_email;
    protected $_password;
    protected $_token;

    public function __construct()
    {
        $this->_baseUrl = env('CM_MIDDLEWARE_URL');
        $this->_email = env('CM_MIDDLEWARE_EMAIL');
        $this->_password = env('CM_MIDDLEWARE_PASSWORD');
        $this->setupMiddlewareLogin();
    }

    function setupMiddlewareLogin()
    {
        try {
            $ch = curl_init();
            $query = http_build_query(array('email' => $this->_email, 'password' => $this->_password));
            curl_setopt($ch, CURLOPT_URL, $this->_baseUrl . '/internal/v1/auth');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

            $headers = array();
            $headers[] = 'Accept: */*';
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                return false;
            }
            curl_close($ch);
            $response = json_decode($result, true);
            if (isset($response['status']) && $response['status'] == true) {
                $this->_token = $response['data']['access_token'];
            }
        } catch (\Exception $ex) {
            Logging::warning($ex->getMessage());
        }
    }

    function registerTenant($data)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->_baseUrl . '/internal/v1/tenant/registered');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

            $headers = array();
            $headers[] = 'Authorization: Bearer ' . $this->_token;
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                return false;
            }
            curl_close($ch);
            $response = json_decode($result, true);
            if (isset($response['status']) && $response['status'] == true) {
                return true;
            }
            return false;
        } catch (\Exception $ex) {
            Logging::warning($ex->getMessage());
        }
    }

    function verifiedTenant($data)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->_baseUrl . '/internal/v1/tenant/verified');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

            $headers = array();
            $headers[] = 'Authorization: Bearer ' . $this->_token;
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                return false;
            }
            curl_close($ch);
            $response = json_decode($result, true);
            if (isset($response['status']) && $response['status'] == true) {
                return true;
            }
            return false;
        } catch (\Exception $ex) {
            Logging::warning($ex->getMessage());
        }
    }

    function billingConfiguredTenant($data)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->_baseUrl . '/internal/v1/tenant/billing/configured');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

            $headers = array();
            $headers[] = 'Authorization: Bearer ' . $this->_token;
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                return false;
            }
            curl_close($ch);
            $response = json_decode($result, true);
            if (isset($response['status']) && $response['status'] == true) {
                return true;
            }
            return false;
        } catch (\Exception $ex) {
            Logging::warning($ex->getMessage());
        }
    }

    function billingCancelledTenant($data)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->_baseUrl . '/internal/v1/tenant/billing/cancelled');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

            $headers = array();
            $headers[] = 'Authorization: Bearer ' . $this->_token;
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                return false;
            }
            curl_close($ch);
            $response = json_decode($result, true);
            if (isset($response['status']) && $response['status'] == true) {
                return true;
            }
            return false;
        } catch (\Exception $ex) {
            Logging::warning($ex->getMessage());
        }
    }

    function updateTenant($data)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->_baseUrl . '/internal/v1/tenant/update');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

            $headers = array();
            $headers[] = 'Authorization: Bearer ' . $this->_token;
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                return false;
            }
            curl_close($ch);
            $response = json_decode($result, true);
            if (isset($response['status']) && $response['status'] == true) {
                return true;
            }
            return false;
        } catch (\Exception $ex) {
            Logging::warning($ex->getMessage());
        }
    }
}
