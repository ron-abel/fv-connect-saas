<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use App\Models\Tenant;
use Exception;

class FilevineService
{
    protected $_baseUrl;
    protected $_accessToken;
    protected $_refreshToken;
    protected $_userId;
    protected $_orgId;
    protected $_apiKey;
    protected $_apiSecret;
    protected $request;

    public function __construct($base_url, $request, $tenant_id = null)
    {

        $this->openFilevineSession($base_url, $request, $tenant_id);
    }

    // Return Org Id
    public function getOrgId()
    {
        return $this->_orgId;
    }

    /**
     * Added by Noor
     * function verify session and api
     *
     * @return boolean
     */
    public function verify_api_and_session()
    {
        if ($this->_accessToken != '' && $this->_refreshToken != '' && $this->_userId != '' && !empty($this->_accessToken) && !empty($this->_refreshToken) && !empty($this->_userId)) {
            return true;
        } else {
            return false;
        }
    }
    // OPEN A FILEVINE SESSION
    function openFilevineSession($base_url, $request, $tenant_id = null)
    {
        // Pre-request script to hash API key, API secret, and timestamp to be used for authentication in the next step to open a Filevine session. Uses Crypot Hash (MD5).
        // Pass the API key, the hashed API key, and the hashed Timestamp in the body and you will be returned an accessToken and a refreshToken to be used in authenticating for API requests to Filevine
        $this->_baseUrl = $base_url;
        $api_url = $base_url . "/session";
        $apiTimestamp = (new \DateTime('UTC'))->format('Y-m-d\TH:i:s.v\Z');

        try {

            if ($request != "" && $request->input('fv_api_key') && $request->input('fv_key_secret')) {
                $this->_apiKey = $request->input('fv_api_key');
                $this->_apiSecret =  $request->input('fv_key_secret');
            } else {
                if (isset($tenant_id) and !empty($tenant_id)) {
                    $subdomain = Tenant::where('id', $tenant_id)->get();
                } else {
                    $subdomainSession = session()->get('subdomain');
                    if (isset($subdomainSession) and !empty($subdomainSession)) {
                        $subdomain = Tenant::where('tenant_name', session()->get('subdomain'))->get();
                    }
                }
                $filevine_config_details = DB::table('config')->where('tenant_id', $subdomain[0]->id)->first();
                $this->_apiKey = $filevine_config_details->fv_api_key;
                $this->_apiSecret =  $filevine_config_details->fv_key_secret;
            }

            // Ordering is important!
            $apiHash = md5($this->_apiKey . "/" . $apiTimestamp . "/" . $this->_apiSecret);
            $params = array(
                'mode'          => 'key',
                'apiKey'        => $this->_apiKey,
                'apiHash'       => $apiHash,
                'apiTimestamp'  => $apiTimestamp,
            );

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "POST",
                CURLOPT_POSTFIELDS      => json_encode($params),
                CURLOPT_HTTPHEADER      => array("Content-Type: application/json"),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                $response = json_decode($response, TRUE);
                if (!isset($response['error'])) {
                    $this->_accessToken = $response['accessToken'];
                    $this->_refreshToken = $response['refreshToken'];
                    $this->_userId = $response['userId'];
                    $this->_orgId = $response['orgId'];
                }
            }
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    /**
     * GET Projects list.
     */
    function getProjectsList($limit = 0, $offset = 0)
    {
        try {
            if ($limit == 0)
                $api_url = $this->_baseUrl . "/core/projects?limit=1000";
            else
                $api_url = $this->_baseUrl . "/core/projects?offset=" . $offset . "&limit=" . $limit;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }

            return $response;
        } catch (\Exception $e) {
            return null;
        }
    }

    // Get Project By LastName
    function getProjectsByLastName($firstname, $lastname, $limit = 0)
    {
        try {
            if ($limit == 0)
                $api_url = $this->_baseUrl . "/core/projects?name=" . $lastname . ",%20" . $firstname . "&limit=1000";
            else
                $api_url = $this->_baseUrl . "/core/projects?name=" . $lastname . ",%20" . $firstname . "&limit=" . $limit;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }

            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // get Projects By Contact id
    function getProjectsByContactId($contactId, $limit = 0, $offset = 0)
    {
        try {
            if ($limit == 0)
                $api_url = $this->_baseUrl . "/core/contacts/" . $contactId . "/projects?limit=1000";
            else
                $api_url = $this->_baseUrl . "/core/contacts/" . $contactId . "/projects?offset=" . $offset . "&limit=" . $limit;

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }

            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }


    // Get Project By Id
    function getProjectsById($projectId)
    {
        try {
            $api_url = $this->_baseUrl . "/core/projects/" . $projectId;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Get Project Details by ProjectId
    function getProjectsDetailsById($projectId)
    {
        try {
            $api_url = $this->_baseUrl . "/core/projects/" . $projectId;

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }


    // Get Project Vitals Info by ProjectId
    function getProjectsVitalsById($projectId)
    {
        try {
            $api_url = $this->_baseUrl . "/core/projects/" . $projectId . "/vitals";

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }

            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }


    // Get Project Team Info by ProjectId
    function getProjectsTeamById($projectId, $offset = 0, $limit = 0)
    {
        try {
            if ($limit == 0)
                $api_url = $this->_baseUrl . "/core/projects/" . $projectId . "/team?limit=1000";
            else
                $api_url = $this->_baseUrl . "/core/projects/" . $projectId . "/team?offset=" . $offset . "&limit=" . $limit;;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }

            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }


    // Get Contact By ContactId
    function getContactByContactId($contactId)
    {
        try {
            $api_url = $this->_baseUrl . "/core/contacts/" . $contactId;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "Content-Type: application/json",
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Get Person Form Team Info
    function getProjectFormsTeamInfo($params)
    {
        try {
            $api_url = $this->_baseUrl . "/core/projects/" . $params['projectId'] . "/forms/{$params['section']}?requestedFields={$params['fields']}";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "Content-Type: application/json",
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }

            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Get Case Summary Team Details: Attorney, Paralegal, and Assistant
    function getCaseSummary_Team($params, $requestedFields = "primaryattorney,paralegal,legalassistant")
    {

        try {
            $api_url = $this->_baseUrl . "/core/projects/" . $params['projectId'] . "/forms/casesummary?requestedFields={$requestedFields}";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "Content-Type: application/json",
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }

            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Get Intake Information
    function getIntakeInfo($projectId)
    {
        try {
            $api_url = $this->_baseUrl . "/core/projects/" . $projectId . "/forms/intake";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "Content-Type: application/json",
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }

            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Get Project Type Object
    function getProjectTypeObject($projectTypeId)
    {
        try {
            $api_url = $this->_baseUrl . "/core/projecttypes/" . $projectTypeId;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "Content-Type: application/json",
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }

            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Get Project Type List
    function getProjectTypeList()
    {
        try {
            $api_url = $this->_baseUrl . "/core/projecttypes?limit=1000";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "Content-Type: application/json",
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }

            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Get Project Type Section List
    function getProjectTypeSectionList($typeid)
    {
        try {
            $api_url = $this->_baseUrl . "/core/projecttypes/{$typeid}/sections?limit=1000";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "Content-Type: application/json",
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }

            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Get Project Type Section Person Field List
    function getProjectTypeSectionFieldList($typeid, $sectionselector)
    {
        try {
            $api_url = $this->_baseUrl . "/core/projecttypes/{$typeid}/sections/{$sectionselector}";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "Content-Type: application/json",
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }

            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Get Project Type Phase List
    function getProjectTypePhaseList($projectTypeId)
    {
        try {
            $api_url = $this->_baseUrl . "/core/projecttypes/" . $projectTypeId . "/phases?limit=1000";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "Content-Type: application/json",
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }

            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }


    // Get Contacts
    function getContacts($params = null)
    {
        try {
            $api_url = $this->_baseUrl . "/core/contacts";

            if ($params) {
                $params = http_build_query($params, "", "&", PHP_QUERY_RFC1738);
                $api_url = $this->_baseUrl . "/core/contacts?" . $params;
            }

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }

            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Get Contact By ContactId
    function getContactByFullName($fullName)
    {
        try {
            $fullName = urlencode($fullName);

            $api_url = $this->_baseUrl . "/core/contacts?fullName=" . $fullName;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "Content-Type: application/json",
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }

            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Get Contact By First name, Last name, Phoneno
    function getContactByClientInfo($firstName, $lastName, $phone = null, $email = null)
    {
        try {
            $firstName = urlencode($firstName);
            $lastName = urlencode($lastName);

            if (!empty($phone) && !empty($email)) {
                $api_url = $this->_baseUrl . "/core/contacts?firstName=" . $firstName . "&lastName=" . $lastName . "&phone=" . $phone . "&email=" . $email;
            } else if (!empty($phone)) {
                $api_url = $this->_baseUrl . "/core/contacts?firstName=" . $firstName . "&lastName=" . $lastName . "&phone=" . $phone;
            } else if (!empty($email)) {
                $api_url = $this->_baseUrl . "/core/contacts?firstName=" . $firstName . "&lastName=" . $lastName . "&email=" . $email;
            } else {
                $api_url = $this->_baseUrl . "/core/contacts?firstName=" . $firstName . "&lastName=" . $lastName;
            }

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "Content-Type: application/json",
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }

            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Create the Contact
    function createContact($params)
    {
        try {
            //         var_dump(json_encode($params));
            $api_url = $this->_baseUrl . "/core/contacts";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_ENCODING        => "",
                CURLOPT_MAXREDIRS       => 10,
                CURLOPT_TIMEOUT         => 0,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST   => "POST",
                CURLOPT_POSTFIELDS      => json_encode($params),
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                )
            ));
            //

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }

            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Get Contact By ContactId
    function updateContact($contactId, $params)
    {
        try {
            $api_url = $this->_baseUrl . "/core/contacts/" . $contactId;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "PATCH",
                CURLOPT_POSTFIELDS      => json_encode($params),
                //                                         CURLOPT_POSTFIELDS      => "{'personTypes':['Client', 'Plaintiff', 'Firm']}",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                    "Content-Type: application/json",
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }

            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }



    // Once we have the tokens have been returned, you can interact with the API. Authentication requires the acessToken as the auth:bearer, x-fv-sessionid (which is the refreshToken), x-fv-orgid (defined in Filevine dev portal), and the x-fv-userid (defined in Filevine dev portal).

    // Get Collection Item
    function getCollectionItem($params)
    {
        try {
            // In this example, we are going to GET from the collections section endpoint a specific collection item to test if it's a "SETTLEMENT" item. URL parameters include the Project ID, the Collection Section ("negotiations"), and the objectId_itemId (44c9d4e6-0fcd-4ade-a3eb-dfbf701f144d).
            $api_url = $this->_baseUrl . "/core/projects/" . $params['projectId'] . "/collections/" . $params['sectionSelector'] . "/" . $params['uniqueId'];
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "Content-Type: application/json",
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }

            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }


    // Get Collection Item
    function getKeys()
    {
        try {
            $api_url = $this->_baseUrl . "/subscriptions/keys";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "Content-Type: application/json",
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }

            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Get Collection Item
    function getEventsList()
    {
        try {
            $api_url = $this->_baseUrl . "/subscriptions/events";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "Content-Type: application/json",
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $this->_apiKey;
                // echo $this->_apiSecret;
                // echo $response;
                $jsonData = json_encode($response, JSON_PRETTY_PRINT);
                // echo str_replace("\n", "<br>", $jsonData);
            }

            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Create the Contact
    function createSubscription($name, $url, $eventIds)
    {
        try {
            $params = array(
                'keyId' => $this->_apiKey, 'name' => $name . " Subscription", "description" => $name . " Subscription Description",
                "endpoint" => $url, "eventIds" => [$eventIds]
            );

            $api_url = $this->_baseUrl . "/subscriptions";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_ENCODING        => "",
                CURLOPT_MAXREDIRS       => 10,
                CURLOPT_TIMEOUT         => 0,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST   => "POST",
                CURLOPT_POSTFIELDS      => json_encode($params),
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                )
            ));
            //

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }

            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Get Subscriptions List
    function getSubscriptionsList()
    {
        try {
            $api_url = $this->_baseUrl . "/subscriptions";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "Content-Type: application/json",
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                $jsonData = json_encode($response, JSON_PRETTY_PRINT);
            }

            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Get Project By Id
    function getNotesByProjectId($projectId)
    {
        try {
            $api_url = $this->_baseUrl . "/core/projects/" . $projectId . "/notes?requestedFields=noteId,isUnread,isCompleted,body,allowEditing,isPinnedToProject,authorId,createdAt";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Get User By Id
    function getUserById($userId, $fields = "userName,user")
    {
        try {
            $api_url = $this->_baseUrl . "/core/users/" . $userId . "?requestedFields=" . $fields;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Get Contact By ContactId
    function updateNoteById($noteId, $params)
    {
        try {
            $api_url = $this->_baseUrl . "/core/notes/" . $noteId;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "PATCH",
                CURLOPT_POSTFIELDS      => json_encode($params),
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                    "Content-Type: application/json",
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    //Create Note
    function createNote($params)
    {
        try {
            $api_url = $this->_baseUrl . "/core/notes/";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "POST",
                CURLOPT_POSTFIELDS      => json_encode($params),
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                    "Content-Type: application/json",
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }


    // Create Comment
    function createNoteComment($noteId, $params)
    {
        try {
            $api_url = $this->_baseUrl . "/core/notes/" . $noteId . "/comments";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_ENCODING        => "",
                CURLOPT_MAXREDIRS       => 10,
                CURLOPT_TIMEOUT         => 0,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST   => "POST",
                CURLOPT_POSTFIELDS      => json_encode($params),
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                )
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }

            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Get Users List
    function getUsersList($fields = "email,user")
    {
        try {
            $api_url = $this->_baseUrl . "/core/users?limit=1000&requestedFields=" . $fields;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Get Auth Users List
    function getAuthUser($fields = "email,user")
    {
        try {
            $api_url = $this->_baseUrl . "/core/users/me?requestedFields=" . $fields;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }


    // Get Task By Id
    function getTaskById($task_id)
    {
        try {
            $api_url = $this->_baseUrl . "/core/tasks/" . $task_id;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Get Task By Id
    function getTeamOrgRoles($projectId)
    {
        try {
            $api_url = $this->_baseUrl . "/core/projects/" . $projectId . "/teamorgroles";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Get Contact Metadata
    function getContactMetadata()
    {
        try {
            $api_url = $this->_baseUrl . "/core/custom-contacts-meta";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }


    // Get All Users List
    function getAllUsersList()
    {
        try {
            $api_url = $this->_baseUrl . "/core/users?limit=1000";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }


    // Create an FV User
    function addNewUser($params)
    {
        try {
            $api_url = $this->_baseUrl . "/core/users";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "POST",
                CURLOPT_POSTFIELDS      => json_encode($params),
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                    "Content-Type: application/json",
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }


    // Delete an FV User
    function deleteNewUser($userid)
    {
        try {
            $api_url = $this->_baseUrl . "/core/users/".$userid;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "DELETE",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                    "Content-Type: application/json",
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Get Appointment Details
    function getAppointments($appointmentId)
    {
        try {
            $api_url = $this->_baseUrl . "/core/appointments/" . $appointmentId;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Get Note By Id
    function getNoteById($note_id)
    {
        try {
            $api_url = $this->_baseUrl . "/core/notes/" . $note_id;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Update Project Details by ProjectId
    function updateProjectsDetailsById($projectId, $params)
    {
        try {
            $api_url = $this->_baseUrl . "/core/projects/" . $projectId;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "PATCH",
                CURLOPT_POSTFIELDS      => json_encode($params),
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }


    /**
     * Add a New Hashtag
     */
    function addNewHashTag($tag, $params)
    {
        try {
            $api_url = $this->_baseUrl . "/core/hashtags/" . $tag;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "POST",
                CURLOPT_POSTFIELDS      => json_encode($params),
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                    "Content-Type: application/json",
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    //Create Task with Note ID
    function createTask($params)
    {
        try {
            $api_url = $this->_baseUrl . "/core/tasks";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "POST",
                CURLOPT_POSTFIELDS      => json_encode($params),
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                    "Content-Type: application/json",
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }


    // Update Subscription Value
    function updateSubscription($subscriptionId, $params)
    {
        try {
            $api_url = $this->_baseUrl . "/subscriptions/" . $subscriptionId;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "PATCH",
                CURLOPT_POSTFIELDS      => json_encode($params),
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                    "Content-Type: application/json",
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Delete Subscription Value
    function deleteSubscription($subscriptionId)
    {
        try {
            $api_url = $this->_baseUrl . "/subscriptions/" . $subscriptionId;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "DELETE",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                    "Content-Type: application/json",
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Create Document Upload URL
    function createDocumentUploadUrl($params)
    {
        try {
            $api_url = $this->_baseUrl . "/core/documents";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_ENCODING        => "",
                CURLOPT_MAXREDIRS       => 10,
                CURLOPT_TIMEOUT         => 0,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST   => "POST",
                CURLOPT_POSTFIELDS      => json_encode($params),
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                )
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }

            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Upload Document To Upload URL
    function uploadDocumentToUploadUrl($url, $file, $type)
    {
        try {
            // $api_url = $this->_baseUrl . "/".$url;
            $api_url = $url;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_ENCODING        => "",
                CURLOPT_MAXREDIRS       => 10,
                CURLOPT_TIMEOUT         => 0,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST   => "PUT",
                CURLOPT_POSTFIELDS      => $file,
                CURLOPT_HTTPHEADER      => array(
                    "Content-Type: " . $type
                )
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                $response = "cURL Error #:" . $err;
            } else {
                // echo $response;
            }

            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Add Document To Project
    function addDocumentToProject($projectId, $documentId, $params)
    {
        try {
            $api_url = $this->_baseUrl . "/core/projects/" . $projectId . "/documents/" . $documentId;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_ENCODING        => "",
                CURLOPT_MAXREDIRS       => 10,
                CURLOPT_TIMEOUT         => 0,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST   => "POST",
                CURLOPT_POSTFIELDS      => json_encode($params),
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                )
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }

            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // get document download url
    function getDocumentDownloadUrl($id)
    {
        $url = "";
        try {
            $api_url = $this->_baseUrl . "/core/documents/" . $id . "/locator";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                // echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }
            $response = json_decode($response, true);
            if (isset($response['documentId'])) {
                $url = $response['url'];
            }
            return $url;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // get documents list
    function getDocumentsList()
    {
        try {
            $api_url = $this->_baseUrl . "/core/documents?limit=1000";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // get project documents list
    function getProjectDocumentsList($projectId)
    {
        try {
            $api_url = $this->_baseUrl . "/core/projects/" . $projectId . "/documents?limit=1000";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // get project folders list
    function getProjectFoldersList($projectId, $limit, $offset, $parentId = "")
    {
        try {
            $api_url = $this->_baseUrl . "/core/folders?projectId=" . $projectId . (!empty($parentId) ? "&parentId=" . $parentId : "") . "&offset=" . $offset . "&limit=" . $limit;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Add Folder To Project
    function createFolderInProject($params)
    {
        try {
            $api_url = $this->_baseUrl . "/core/folders";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_ENCODING        => "",
                CURLOPT_MAXREDIRS       => 10,
                CURLOPT_TIMEOUT         => 0,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST   => "POST",
                CURLOPT_POSTFIELDS      => json_encode($params),
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                )
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }

            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // get single folder
    function getSingleFolder($folderId)
    {
        try {
            $api_url = $this->_baseUrl . "/core/folders/" . $folderId;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Add Hash Tags To Document Metadata
    function addHashTagsToDocument($documentId, $params)
    {
        try {
            $api_url = $this->_baseUrl . "/core/documents/" . $documentId;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_ENCODING        => "",
                CURLOPT_MAXREDIRS       => 10,
                CURLOPT_TIMEOUT         => 0,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST   => "PATCH",
                CURLOPT_POSTFIELDS      => json_encode($params),
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                )
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                // echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }

            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Get Collection Item List For a Project
    function getProjectCollectionItemList($params)
    {
        try {
            $api_url = $this->_baseUrl . "/core/projects/" . $params['projectId'] . "/collections/" . $params['sectionSelector'];
            //  echo $api_url; exit;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "Content-Type: application/json",
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Add document into static section form
    function updateStaticForm($projectId, $sectionselector, $params)
    {
        try {
            $api_url = $this->_baseUrl . "/core/projects/" . $projectId . '/forms/' . $sectionselector;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_ENCODING        => "",
                CURLOPT_MAXREDIRS       => 10,
                CURLOPT_TIMEOUT         => 0,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST   => "PATCH",
                CURLOPT_POSTFIELDS      => json_encode($params),
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                )
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Create collection ttem
    function createCollectionItem($projectId, $sectionselector, $params)
    {
        try {
            $api_url = $this->_baseUrl . "/core/projects/" . $projectId . '/collections/' . $sectionselector;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_ENCODING        => "",
                CURLOPT_MAXREDIRS       => 10,
                CURLOPT_TIMEOUT         => 0,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST   => "POST",
                CURLOPT_POSTFIELDS      => json_encode($params),
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                )
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Get list of child folder
    function getChildFolders($folderId)
    {
        try {
            $api_url = $this->_baseUrl . "/core/folders/" . $folderId . "/children";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                // echo $response;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Create Project
    function createProject($params)
    {
        try {
            $api_url = $this->_baseUrl . "/core/projects";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_ENCODING        => "",
                CURLOPT_MAXREDIRS       => 10,
                CURLOPT_TIMEOUT         => 0,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST   => "POST",
                CURLOPT_POSTFIELDS      => json_encode($params),
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                )
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Toggle Section Visibility
    function toggleSectionVisibility($projectId, $params)
    {
        try {
            $api_url = $this->_baseUrl . "/core/projects/" . $projectId . '/sectionvisibility';
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_ENCODING        => "",
                CURLOPT_MAXREDIRS       => 10,
                CURLOPT_TIMEOUT         => 0,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST   => "POST",
                CURLOPT_POSTFIELDS      => json_encode($params),
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                )
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // get project Task list
    function getProjectTaskList($projectId)
    {
        try {
            $api_url = $this->_baseUrl . "/core/projects/" . $projectId . "/tasks?limit=1000";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Delete Task or Kill Task
    function deleteTask($taskId)
    {
        try {
            $api_url = $this->_baseUrl . "/core/tasks/" . $taskId;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "DELETE",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                    "Content-Type: application/json",
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // get team member picture
    function getTeamMemberPicture($imageId)
    {
        try {
            $api_url = $this->_baseUrl . "/core/images/" . $imageId;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_ENCODING        => "",
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                return json_encode(array('success' => false, 'error' => $err));
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Get Project Appointment
    function getProjectAppointments($projectId)
    {
        try {
            $api_url = $this->_baseUrl . "/core/projects/" . $projectId . "/appointments?fromDateTimeUtc=" . date('Y-01-01');
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }


    // Update Appointment by Id
    function updateAppointment($appointmentId, $params)
    {
        try {
            $api_url = $this->_baseUrl . "/core/appointments/" . $appointmentId;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "PATCH",
                CURLOPT_POSTFIELDS      => json_encode($params),
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                    "Content-Type: application/json",
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Get collection selector field item list
    function getProjectCollectionSelectorItem($projectId, $sectionselector, $item = null)
    {
        try {
            $api_url = $this->_baseUrl . "/core/projects/" . $projectId . '/collections/' . $sectionselector;
            if (!empty($item)) {
                $api_url .= '/' . $item;
            }
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "Content-Type: application/json",
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Update collection section selector field value
    function updateProjectCollectionSelectorItem($projectId, $sectionselector, $itemId, $params)
    {
        try {
            $api_url = $this->_baseUrl . "/core/projects/" . $projectId . '/collections/' . $sectionselector . '/' . $itemId;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_ENCODING        => "",
                CURLOPT_MAXREDIRS       => 10,
                CURLOPT_TIMEOUT         => 0,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST   => "PATCH",
                CURLOPT_POSTFIELDS      => json_encode($params),
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                )
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }


    /**
     * Delete Project Contact
     * should use the projectContactID correctly.
     */
    function deleteProjectContact($projectId, $projectContactId)
    {
        try {
            $api_url = $this->_baseUrl . "/core/projects/" . $projectId . "/contacts/" . $projectContactId;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "DELETE",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                    "Content-Type: application/json",
                ),
            ));
            $response = curl_exec($curl);

            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    /**
     * Archive Project
     */
    function archiveProject($projectId)
    {
        try {
            $api_url = $this->_baseUrl . "/core/projects/" . $projectId;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "DELETE",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                    "Content-Type: application/json",
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }


    //Get Project Contact List
    function getProjectContactList($projectId)
    {
        try {
            $api_url = $this->_baseUrl . "/core/projects/" . $projectId . "/contacts?limit=1000";
            $curl = curl_init();
            curl_setopt_array(
                $curl,
                array(
                    CURLOPT_URL             => $api_url,
                    CURLOPT_ENCODING        => "",
                    CURLOPT_RETURNTRANSFER  => true,
                    CURLOPT_CUSTOMREQUEST   => "GET",
                    CURLOPT_HTTPHEADER      => array(
                        "Authorization: Bearer " . $this->_accessToken,
                        "x-fv-sessionid: " . $this->_refreshToken,
                        "Content-Type: application/json",
                        "x-fv-orgid: " . $this->_orgId,
                        "x-fv-userid: " . $this->_userId,
                    ),
                )
            );
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                return json_encode(array('success' => false, 'error' => $err));
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Add Project Team Member
    function addProjectTeamMember($projectId, $params)
    {
        try {
            $api_url = $this->_baseUrl . "/core/projects/" . $projectId . "/team";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_ENCODING        => "",
                CURLOPT_MAXREDIRS       => 10,
                CURLOPT_TIMEOUT         => 0,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST   => "POST",
                CURLOPT_POSTFIELDS      => json_encode($params),
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                )
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Remove Project Team Member
    function removeProjectTeamMember($projectId, $userId)
    {
        try {
            $api_url = $this->_baseUrl . "/core/projects/" . $projectId . "/team/" . $userId;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "DELETE",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                    "Content-Type: application/json",
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }


    // Get Project List
    function getProjectByContactProjectLink($project_link)
    {
        try {
            $api_url = $this->_baseUrl . "/core" . $project_link;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Get Document Details
    function getDocument($documentId)
    {
        try {
            $api_url = $this->_baseUrl . "/core/documents/" . $documentId;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CUSTOMREQUEST   => "GET",
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    //Get Initializer Report
    function getInitializerReportTenant($reportId)
    {
        try {
            $api_url = $this->_baseUrl . "/core/initializer/saved-report/" . $reportId;
            $curl = curl_init();
            curl_setopt_array(
                $curl,
                array(
                    CURLOPT_URL             => $api_url,
                    CURLOPT_ENCODING        => "",
                    CURLOPT_RETURNTRANSFER  => true,
                    CURLOPT_CUSTOMREQUEST   => "GET",
                    CURLOPT_HTTPHEADER      => array(
                        "Authorization: Bearer " . $this->_accessToken,
                        "x-fv-sessionid: " . $this->_refreshToken,
                        "Content-Type: application/json",
                        "x-fv-orgid: " . $this->_orgId,
                        "x-fv-userid: " . $this->_userId,
                    ),
                )
            );
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                return json_encode(array('success' => false, 'error' => $err));
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    // Add Project Team Member
    function addInitializerReportToTenant($encryptedData)
    {
        try {
            $params = [
                'encryptedData' => $encryptedData,
                'asUser' => [
                    'native' => $this->_userId
                ]
            ];
            $api_url = $this->_baseUrl . "/core/initializer/saved-report";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $api_url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_ENCODING        => "",
                CURLOPT_MAXREDIRS       => 10,
                CURLOPT_TIMEOUT         => 0,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST   => "POST",
                CURLOPT_POSTFIELDS      => json_encode($params),
                CURLOPT_HTTPHEADER      => array(
                    "Authorization: Bearer " . $this->_accessToken,
                    "x-fv-sessionid: " . $this->_refreshToken,
                    "Content-Type: application/json",
                    "x-fv-orgid: " . $this->_orgId,
                    "x-fv-userid: " . $this->_userId,
                )
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                return json_encode(array('success' => false, 'error' => $err));
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

    //Get Run Report
    function getRunReportTenant($reportId)
    {
        try {
            $api_url = $this->_baseUrl . "/core/reports/" . $reportId . "?limit=1000";
            $curl = curl_init();
            curl_setopt_array(
                $curl,
                array(
                    CURLOPT_URL             => $api_url,
                    CURLOPT_ENCODING        => "",
                    CURLOPT_RETURNTRANSFER  => true,
                    CURLOPT_CUSTOMREQUEST   => "GET",
                    CURLOPT_HTTPHEADER      => array(
                        "Authorization: Bearer " . $this->_accessToken,
                        "x-fv-sessionid: " . $this->_refreshToken,
                        "Content-Type: application/json",
                        "x-fv-orgid: " . $this->_orgId,
                        "x-fv-userid: " . $this->_userId,
                    ),
                )
            );
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                return json_encode(array('success' => false, 'error' => $err));
            }
            return $response;
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'error' => $e->getMessage()));
        }
    }

}
