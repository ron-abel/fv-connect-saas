<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Blacklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as Logging;
use Illuminate\Support\Facades\DB;

use App\Models\Log;
use App\Models\LegalteamConfig;
use App\Models\Tenant;
use App\Models\ClientAuthFailedSubmitLog;
use App\Models\TenantLive;
use App\Services\FilevineService;
use Stichoza\GoogleTranslate\GoogleTranslate;

class LoginController extends Controller
{
    public $domainName;
    public $cur_tenant_id;
    public function __construct()
    {
        Controller::setSubDomainName();
        $this->domainName = session()->get('subdomain');
        $this->cur_tenant_id = session()->get('tenant_id');
    }

    /**
     * [GET] Login Page for Client
     */
    public function index(Request $request)
    {
        try {
            session()->forget('service_sid');
            if ($this->cur_tenant_id) {
                $tenantLiveInfo = TenantLive::where('tenant_id', $this->cur_tenant_id)->first();
            } else {
                return back()->with('botherror', 'Invalid Tenant!');
            }
            return $this->_loadContent('client.pages.login', ['tenantLiveInfo' => $tenantLiveInfo]);
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
     * [POST] Login for Client
     */
    public function login(Request $request)
    {
        try {
            $tr = new GoogleTranslate();
            $tr->setSource();
            $tr->setTarget(!is_null(session()->get('lang')) ? session()->get('lang') : 'en');
            $request->session()->put('sms_servsid_array', array());
            $tenant_id = $this->cur_tenant_id;
            if (!$tenant_id) {
                return back()->with('botherror', $tr->translate('Invalid Tenant!'));
            }
            $Tenant = Tenant::find($tenant_id);
            $legalteam_config =  LegalteamConfig::where('tenant_id', $tenant_id)->get();
            $config_details = DB::table('config')->where('tenant_id', $tenant_id)->first();
           
            $PhoneNo = $request->PhoneNo;
            $EmailAddress = $request->EmailAddress;
            $try_with = !is_null(session()->get('try_with')) ? session()->get('try_with') : '';
            if (!empty($PhoneNo)) {
                $try_with .= $try_with . 'phone';
            }

            if (!empty($EmailAddress)) {
                $try_with .= $try_with . 'email';
            }
            $request->session()->put('try_with', $try_with);

            if (empty($request->FirstName) || empty($request->LastName) || (empty($PhoneNo) && empty($EmailAddress))) {
                return back()->with('someError', $tr->translate('Please enter all client info'));
            }

            $all_projects_data = [];

            if (isset($request->FirstName) && isset($request->LastName) && (isset($PhoneNo)) ||  isset($EmailAddress)) {
                if (trim($request->FirstName) != "" && trim($request->LastName) != "" && (trim($PhoneNo) != "" || trim($EmailAddress) != "")) {


                    $apiurl = config('services.fv.default_api_base_url');
                    if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                        $apiurl = $Tenant->fv_api_base_url;
                    }
                    $filevine_api = new FilevineService($apiurl, "");

                    if (!$filevine_api->verify_api_and_session()) {
                        return back()->with('someError', $tr->translate('Invalid FileVine Response. Please ask the admin manager!'));
                    }
                    $last_name = $request->LastName;
                    $first_name = $request->FirstName;
                    $lookup_email = trim($EmailAddress);
                    $replaceStr = array('.', '(', ')', '/', '-', '_');
                    $phone_no = trim($PhoneNo);
                    $phone_no = str_replace($replaceStr, '', $phone_no);
                    // place search log for params
                    $insert = Log::create([
                        'tenant_id'        => $tenant_id,
                        'Lookup_IP'        => $request->getClientIp(),
                        'Lookup_Name'      => $first_name . ' ' . $last_name,
                        'Lookup_Phone_num' => $phone_no,
                        'lookup_email' => $lookup_email,
                    ]);

                    $last_insert_id = $insert->id;
                    $request->session()->put('current_log_id', $last_insert_id);

                    $log_id_session = !is_null(session()->get('log_id_session')) ? session()->get('log_id_session') : '';
                    $log_id_session .= $log_id_session . ',' . $last_insert_id;
                    $request->session()->put('log_id_session', $log_id_session);


                    $phone_no = strlen($phone_no) > 10 ? ltrim($phone_no, '1') : $phone_no;

                    $client_object['items'] = [];
                    $match_field = '';

                    if (!empty($phone_no) && !empty($lookup_email)) {
                        $client_object = json_decode($filevine_api->getContactByClientInfo($first_name, $last_name, $phone_no, $lookup_email), TRUE);
                        $match_field = 'phone_email';
                    }

                    if (count($client_object['items']) === 0 && !empty($phone_no)) {
                        $client_object = json_decode($filevine_api->getContactByClientInfo($first_name, $last_name, $phone_no), TRUE);
                        $match_field = 'phone';
                    }

                    if (count($client_object['items']) === 0 && !empty($lookup_email)) {
                        $client_object = json_decode($filevine_api->getContactByClientInfo($first_name, $last_name, null, $lookup_email), TRUE);
                        $match_field = 'email';
                    }

                    if (count($client_object['items']) === 0) {
                        Log::where('id', $last_insert_id)->update([
                            'note'   => 'Invalid Client Info.'
                        ]);
                        return back()->with('someError', $tr->translate('Invalid Client Info.'))->with('submit_field', $match_field)->withInput();
                    }

                    if (count($client_object['items']) > 1) {
                        Log::where('id', $last_insert_id)->update([
                            'note'   => 'Found multiple clients for the same client info.'
                        ]);
                        return back()->with('someError', $tr->translate('Found multiple clients for the same client info.'));
                    }

                    $client_object = collect($client_object['items'])->first();
                    if ($client_object) {
                        $contact_id = isset($client_object['personId']) ? $client_object['personId']['native'] : null;

                        // checking if client or project is in blacklist or not
                        $clientBlacklisted = Blacklist::where('fv_client_id', $contact_id)->where('is_allow_client_potal', 0)->whereNull('fv_project_id')->count();
                        if ($clientBlacklisted > 0) {
                            Log::where('id', $last_insert_id)->update([
                                'note'   => 'We are not able to retrieve your case details at this time. Please check back later.'
                            ]);
                            return redirect()->back()->with('someError', $tr->translate("We are not able to retrieve your case details at this time. Please check back later."));
                        }

                        $blacklistedProjectIds = Blacklist::select('fv_project_id')->where('fv_client_id', $contact_id)
                            ->where('is_allow_client_potal', 1)
                            ->get();

                        if ($blacklistedProjectIds) {
                            $blacklistedProjectIds = $blacklistedProjectIds->pluck('fv_project_id')->toArray();
                        } else {
                            $blacklistedProjectIds = [];
                        }

                        // phone validation logic when client has multiple data from the first, last name
                        // $client_phones = isset($client_object['phones']) ? $client_object['phones'] : [];
                        // $phone_validate = false;
                        // if (count($client_phones) > 0) {
                        //     foreach ($client_phones as $key => $phone) {
                        //         if (($phone_no === $phone['number']) || ($phone_no === $phone['rawNumber'])) {
                        //             $phone_validate = true;
                        //             break;
                        //         }
                        //     }
                        // }
                        // if (!$phone_validate){
                        //     return redirect()->back()->with('someError', "Invalid Client Phone Number.");
                        // }

                        $offset = 0;
                        $limit = 1000;
                        $project_ids = [];
                        $all_projects_data = null;

                        do {
                            $client_projects = json_decode($filevine_api->getProjectsByContactId($contact_id, $limit, $offset), TRUE);
                            $next_link = trim($client_projects['links']['next']);
                            $data = null;

                            if (isset($client_projects['items'])) {
                                foreach ($client_projects['items'] as $each_project) {
                                    // validate if the project['project']['phaseName'] is "Archived"
                                    if (isset($each_project['project']['phaseName'], $config_details->is_show_archieved_phase) && $each_project['project']['phaseName'] == 'Archived' && $config_details->is_show_archieved_phase == 0) {
                                        continue;
                                    }

                                    $params['projectId'] = $each_project['projectId']['native'];
                                    if (in_array($params['projectId'], $project_ids) || in_array($params['projectId'], $blacklistedProjectIds)) {
                                        continue;
                                    }

                                    // check the client role of the project.
                                    $contact_role = $each_project['role'] ?? "";
                                    if (strtolower($contact_role) !== 'client') {
                                        continue;
                                    }

                                    $project_ids[] = $params['projectId'];

                                    // $casesummary = json_decode($filevine_api->getCaseSummary_Team($params), TRUE);
                                    // $intakeInfo = json_decode($filevine_api->getIntakeInfo($params['projectId']), TRUE);
                                    // $data = $this->getResult($legalteam_config, $casesummary, $each_project, $intakeInfo);
                                    // if (isset($data, $data['success']) && $data['success'] == false) {
                                    //     return redirect()->back()->with('someError', $data['message']);
                                    // }


                                    $data = $each_project;
                                    $all_projects_data[] = $data;
                                }
                                if (isset($data['project']['clientName'], $data['projectId']['native'])) {
                                    // update search logs if result found
                                    Log::where('id', $last_insert_id)->update([
                                        'Result_Client_Name'   => $data['project']['clientName'],
                                        'Result_Project_Id'    => $data['projectId']['native'],
                                        'fv_client_id'         => $contact_id
                                    ]);
                                }
                            }
                            $offset += $limit;
                        } while ($next_link);

                        if ($all_projects_data == null) {
                            Log::where('id', $last_insert_id)->update([
                                'note'   => 'No Project from the Client Name.'
                            ]);
                            return redirect()->back()->with('someError', $tr->translate("No Project from the Client Name."));
                        }

                        if (count($project_ids) > 0) {
                            $request->session()->put('result', $all_projects_data);
                            $request->session()->put('contact_id', $contact_id);
                            $request->session()->put('log_id', $last_insert_id);
                            $request->session()->put('match_field', $match_field);

                            if (empty($phone_no)) $phone_no = $lookup_email;

                            return redirect()->to('/2fa_verify/' . $data['projectId']['native'] . '/' . $phone_no);
                        }

                        Log::where('id', $last_insert_id)->update([
                            'note'   => 'No project for this client info.'
                        ]);
                        return back()->with('noprojectclient', 'No project for this client info');
                    }
                    Log::where('id', $last_insert_id)->update([
                        'note'   => 'Client not found.'
                    ]);
                    return redirect()->back()->with('someError', $tr->translate("Client not found."));
                } else {
                    return back()->with('phoneclient', $tr->translate('Invalid Client Info.'));
                }
            }
        } catch (\Exception $e) {
            $exception_json = json_encode($e);
            Logging::warning($e->getMessage());
            Logging::warning($exception_json);
            return back()->with('someError', $e->getMessage());
        }
    }

    /**
     *  Get Legal config data for client
     *  don't use it now.
     */
    public function getResult($legalteam_config, $casesummary, $project, $intakeInfo)
    {
        try {
            $result = array(
                'success' => False, 'message' => '',
                'clientName' => '', 'projectName' => '', 'projectEmail' => '', 'caseType' => '', 'createdDate' => '', 'clientNativeId' => '', 'clientPartnerId' => '',
                'attorney' => array('status' => 0, 'name' => '', 'email' => '', 'phone' => ''),
                'paralegal' => array('status' => 0, 'name' => '', 'email' => '', 'phone' => ''),
                'assistant' => array('status' => 0, 'name' => '', 'email' => '', 'phone' => ''),
                'clientRelationsManager' => array('status' => 0, 'name' => '', 'email' => '', 'phone' => '')
            );

            if (empty($project) || $project == null) {
                $result['message'] = "No Project from Filevine!";
                return $result;
            }

            if ($legalteam_config->count() == 4) {
                if (!is_null($casesummary)) {
                    // Attorney
                    $result['attorney']['status'] = $legalteam_config[2]->status;
                    if ($result['attorney']['status'] == 2) {
                        $result['attorney']['name']     = $legalteam_config[2]->full_name;
                        $result['attorney']['email']    = $legalteam_config[2]->email;
                        $result['attorney']['phone']    = $legalteam_config[2]->phone_number;
                    } else if ($result['attorney']['status'] == 1) {
                        if (isset($casesummary['primaryattorney'], $casesummary['primaryattorney']['fullname'], $casesummary['primaryattorney']['emails'][0]['address'], $casesummary['primaryattorney']['phones'][0]['number'])) {
                            $result['attorney']['name']     = $casesummary['primaryattorney']['fullname'];
                            $result['attorney']['email']    = $casesummary['primaryattorney']['emails'][0]['address'];
                            $result['attorney']['phone']    = $casesummary['primaryattorney']['phones'][0]['number'];
                        }
                    }

                    // Paralegal
                    $result['paralegal']['status'] = $legalteam_config[0]->status;
                    if ($result['paralegal']['status'] == 2) {
                        $result['paralegal']['name']    = $legalteam_config[0]->full_name;
                        $result['paralegal']['email']   = $legalteam_config[0]->email;
                        $result['paralegal']['phone']   = $legalteam_config[0]->phone_number;
                    } else if ($result['paralegal']['status'] == 1) {
                        if (isset($casesummary['paralegal'], $casesummary['paralegal']['fullname'], $casesummary['paralegal']['emails'][0]['address'], $casesummary['paralegal']['phones'][0]['number'])) {
                            $result['paralegal']['name']    = $casesummary['paralegal']['fullname'];
                            $result['paralegal']['email']   = $casesummary['paralegal']['emails'][0]['address'];
                            $result['paralegal']['phone']   = $casesummary['paralegal']['phones'][0]['number'];
                        }
                    }

                    // Assistant
                    $result['assistant']['status'] = $legalteam_config[1]->status;
                    if ($result['assistant']['status'] == 2) {
                        $result['assistant']['name']   = $legalteam_config[1]->full_name;
                        $result['assistant']['email']  = $legalteam_config[1]->email;
                        $result['assistant']['phone']  = $legalteam_config[1]->phone_number;
                    } else if ($result['assistant']['status'] == 1) {
                        if (isset($casesummary['legalassistant'], $casesummary['legalassistant']['fullname'], $casesummary['legalassistant']['emails'][0]['address'], $casesummary['legalassistant']['phones'][0]['number'])) {
                            $result['assistant']['name']   = $casesummary['legalassistant']['fullname'];
                            $result['assistant']['email']  = $casesummary['legalassistant']['emails'][0]['address'];
                            $result['assistant']['phone']  = $casesummary['legalassistant']['phones'][0]['number'];
                        }
                    }

                    // Client Relations Manager
                    $result['clientRelationsManager']['status'] = $legalteam_config[3]->status;
                    if ($result['clientRelationsManager']['status'] == 1) {
                        $result['clientRelationsManager']['name']   = $legalteam_config[3]->full_name;
                        $result['clientRelationsManager']['email']  = $legalteam_config[3]->email;
                        $result['clientRelationsManager']['phone']  = $legalteam_config[3]->phone_number;
                    }

                    if (isset($project['message'])) {
                        $result['message'] = "No Project from Filevine!";
                        return $result;
                    }



                    // Project Details
                    $result['success']       = true;
                    $result['projectId']     = $project['projectId']['native'];
                    $result['projectTypeId'] = $project['project']['projectTypeId']['native'];
                    $result['clientName']    = $project['project']['clientName'];
                    $result['clientNativeId'] = $project['project']['clientId']['native'];
                    $result['clientPartnerId'] = $project['project']['clientId']['partner'];
                    $result['projectName']   = $project['project']['projectName'];
                    $result['projectEmail']  = $project['project']['projectEmailAddress'];
                    $result['caseType']      = $project['project']['projectTypeCode'];
                    $result['phaseName']     = $project['project']['phaseName'];
                    $result['phaseId']       = $project['project']['phaseId']['native'];
                    $result['createdDate']   = date('m/d/Y', strtotime($intakeInfo['dateofintake']));

                    return $result;
                } else {
                    $result['message'] = "No CaseSummary from the FV Project!";
                    return $result;
                }
            } else {
                $result['message'] = "Invalid Legal Team Config!";
                return $result;
            }
        } catch (\Exception $e) {
            $exception_json = json_encode($e);
            Logging::warning($e->getMessage());
            Logging::warning($exception_json);
            return $result['message'] = $e->getMessage();
        }
    }

    /**
     *  [GET] Logout for Client
     */
    public function logout()
    {
        $lang = '';
        if (!is_null(session('lang'))) {
            $lang = session('lang');
        }
        session()->flush();
        if ($lang) {
            session()->put('lang', $lang);
        }
        session()->forget('match_field');
        session()->forget('try_with');
        return redirect()->route('client', ['subdomain' => $this->domainName]);
    }

    /**
     *  [POST] Submit Information of ClientP
     */
    public function submitInformation(Request $request)
    {
        try {
            $insert = ClientAuthFailedSubmitLog::create([
                'tenant_id'        => $this->cur_tenant_id,
                'client_ip'        => $request->getClientIp(),
                'lookup_first_name'      => $request->modal_name,
                'lookup_last_name'      => $request->modal_last_name,
                'lookup_phone' => $request->modal_phone_no,
                'lookup_email' => $request->modal_email_address
            ]);
            
            $insert_id = $insert->id;

            $log_id_session = !is_null(session()->get('log_id_session')) ? session()->get('log_id_session') : '';
            session()->forget('log_id_session');
            $log_id_session = ltrim($log_id_session, ',');
            $log_ids = explode(',', $log_id_session);
            if (count($log_ids)) {
                Log::whereIn('id', $log_ids)->update([
                    'client_auth_failed_submit_log_id' => $insert_id
                ]);
            }

            return redirect()->route('client', ['subdomain' => $this->domainName])->with('success', 'Submitted!');
        } catch (\Exception $e) {
            Logging::warning($e->getMessage());
        }
    }
}
