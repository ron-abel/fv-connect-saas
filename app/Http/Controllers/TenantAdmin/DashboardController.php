<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use PhpSpellcheck\SpellChecker\Aspell;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as Logging;
use App\Models\Log;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTime;
use DatePeriod;
use DateInterval;
use Exception;
use App\Models\Feedbacks;
use App\Services\ExportService;
use Auth;
use App\Models\Tenant;
use App\Models\Blacklist;
use App\Models\ClientAuthFailedSubmitLog;
use App\Models\TenantNotificationLog;

use App\Services\FilevineService;
use App\Services\SendGridServices;
use App\Services\TwilioService;

class DashboardController extends Controller
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
     * [GET] Dashboard Page for Admin
     */
    public function index()
    {
        try {

            $tenant_id = $this->cur_tenant_id;
            $startDate = request()->has('log_start_date') ? date("Y-m-d", strtotime(request()->get('log_start_date'))) : Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = request()->has('log_end_date') ? date("Y-m-d", strtotime(request()->get('log_end_date'))) : Carbon::now()->format('Y-m-d');
            $startDateLog = request()->has('log_start') ? date("Y-m-d", strtotime(request()->get('log_start'))) : Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDateLog = request()->has('log_end') ? date("Y-m-d", strtotime(request()->get('log_end'))) : Carbon::now()->format('Y-m-d');
            $startDateMsg = request()->has('msg_start_date') ? date("Y-m-d", strtotime(request()->get('msg_start_date'))) : Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDateMsg = request()->has('msg_end_date') ? date("Y-m-d", strtotime(request()->get('msg_end_date'))) : Carbon::now()->format('Y-m-d');
            $type_of_line = request()->has('type_of_line') ? request()->get('type_of_line') : "";
            $login_status = request()->has('login_status') ? request()->get('login_status') : "";
            $client_name = request()->has('client_name') ? request()->get('client_name') : "";
            $startDateLogTrouble = request()->has('startDateLogTrouble') ? date("Y-m-d", strtotime(request()->get('startDateLogTrouble'))) : Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDateLogTrouble = request()->has('endDateLogTrouble') ? date("Y-m-d", strtotime(request()->get('endDateLogTrouble'))) : Carbon::now()->format('Y-m-d');

            $log = Log::Where('tenant_id', $tenant_id);
            if ($login_status == "1") {
                $log = $log->where('Result', $login_status);
            } else if ($login_status == "0") {
                $log = $log->where(function ($query) {
                    $query->where("Result", 0)
                        ->orWhereNull('Result');
                });
            }
            if (!empty($client_name)) {
                $log = $log->where('Result_Client_Name', 'like', '%' . $client_name . '%');
            }
            $log = $log->whereDate('created_at', '>=', $startDateLog)
                ->whereDate('created_at', '<=', $endDateLog)
                ->orderBy('id', 'DESC')
                ->paginate(50, ['*'], 'log');
            $now = Carbon::now()->format('Y-m-d');
            $start_week = Carbon::now()->subDays(6)->format('Y-m-d');
            $week_date = CarbonPeriod::create($start_week, $now)->toArray();
            $formatted_dates = [];
            foreach ($week_date as $date) {
                $formatted_dates[] = $date->format("d-M");
            }

            $feedbacks = Feedbacks::Where('tenant_id', $tenant_id)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate . ' 23:59:59')
                ->orderBy('id', 'DESC')
                ->paginate(50, ['*'], 'feedbacks');

            $mass_message_logs = DB::table('mass_messages')
                ->join('mass_message_logs', 'mass_messages.id', '=', 'mass_message_logs.mass_message_id')
                ->select('mass_message_logs.person_name as client_name', 'mass_message_logs.person_number as to_number', 'mass_messages.message_body as message', 'mass_message_logs.from_number', 'mass_message_logs.is_inbound as msg_type', DB::raw("'MassMessage' as type_of_line"), 'mass_message_logs.job_id',  'mass_message_logs.created_at as created_at', 'mass_message_logs.id as id')
                ->where('mass_messages.tenant_id', $tenant_id)
                ->where('mass_message_logs.is_inbound', 1)
                ->where('mass_message_logs.created_at', '>=', $startDateMsg)
                ->where('mass_message_logs.created_at', '<=', $endDateMsg . ' 23:59:59');

            $google_review_reply_messages = DB::table('auto_note_google_review_messages as angrm')
                ->join('fv_clients', 'angrm.client_id', '=', 'fv_clients.id')
                ->select('fv_clients.fv_client_name as client_name', 'angrm.to_number', 'angrm.message_body as message', 'angrm.from_number', 'angrm.msg_type', 'angrm.type_of_line', DB::raw("Null as job_id"), 'angrm.created_at as created_at', 'angrm.id as id')
                ->where(['angrm.tenant_id' => $tenant_id])
                ->where('angrm.created_at', '>=', $startDateMsg)
                ->where('angrm.created_at', '<=', $endDateMsg . ' 23:59:59');


            //Automated Workflow SMS
            $aw_sms = DB::table('automated_workflow_action_logs as aw_action_log')
                ->join('automated_workflow_actions', 'aw_action_log.action_id', '=', 'automated_workflow_actions.id')
                ->join('automated_workflow_initial_actions', 'automated_workflow_actions.automated_workflow_initial_action_id', '=', 'automated_workflow_initial_actions.id')
                ->join('fv_clients', 'aw_action_log.fv_client_id', '=', 'fv_clients.fv_client_id')
                ->select('fv_clients.fv_client_name as client_name', 'aw_action_log.sms_phones as to_number', 'aw_action_log.note_body as message', DB::raw("Null as from_number"), DB::raw("'out' as msg_type"), DB::raw("'AW SMS' as type_of_line"), DB::raw("Null as job_id"), 'aw_action_log.created_at as created_at', 'aw_action_log.id as id')
                ->where(['automated_workflow_initial_actions.tenant_id' => $tenant_id])
                ->where(['automated_workflow_initial_actions.action_short_code' => '1'])
                ->where(['aw_action_log.tenant_id' => $tenant_id])
                ->where(['fv_clients.tenant_id' => $tenant_id])
                ->where('aw_action_log.created_at', '>=', $startDateMsg)
                ->where('aw_action_log.created_at', '<=', $endDateMsg . ' 23:59:59');

            //Automated Workflow Email
            $aw_email = DB::table('automated_workflow_action_logs as aw_action_log')
                ->join('automated_workflow_actions', 'aw_action_log.action_id', '=', 'automated_workflow_actions.id')
                ->join('automated_workflow_initial_actions', 'automated_workflow_actions.automated_workflow_initial_action_id', '=', 'automated_workflow_initial_actions.id')
                ->join('fv_clients', 'aw_action_log.fv_client_id', '=', 'fv_clients.fv_client_id')
                ->select('fv_clients.fv_client_name as client_name', 'aw_action_log.emails as to_number', 'aw_action_log.note_body as message', DB::raw("Null as from_number"), DB::raw("'email' as msg_type"), DB::raw("'AW Email' as type_of_line"), DB::raw("Null as job_id"), 'aw_action_log.created_at as created_at', 'aw_action_log.id as id')
                ->where(['automated_workflow_initial_actions.tenant_id' => $tenant_id])
                ->where(['automated_workflow_initial_actions.action_short_code' => '12'])
                ->where(['aw_action_log.tenant_id' => $tenant_id])
                ->where(['fv_clients.tenant_id' => $tenant_id])
                ->where('aw_action_log.created_at', '>=', $startDateMsg)
                ->where('aw_action_log.created_at', '<=', $endDateMsg . ' 23:59:59');


            if (!empty($type_of_line)) {
                if ($type_of_line == "AWSMS") {
                    $google_review_reply_messages = $aw_sms->orderBy('created_at', 'DESC')
                        ->orderBy('id', 'DESC')
                        ->paginate(50, ['*'], 'automated_workflow_action_logs');
                } else if ($type_of_line == "AWEmail") {
                    $google_review_reply_messages = $aw_email->orderBy('created_at', 'DESC')
                        ->orderBy('id', 'DESC')
                        ->paginate(50, ['*'], 'automated_workflow_action_logs');
                } else if ($type_of_line != "MassMessage") {
                    $google_review_reply_messages = $google_review_reply_messages->where('angrm.type_of_line', '=', $type_of_line)
                        ->orderBy('created_at', 'DESC')
                        ->orderBy('id', 'DESC')
                        ->paginate(50, ['*'], 'google_review_reply_messages');
                } else {
                    $google_review_reply_messages = $google_review_reply_messages->where('angrm.type_of_line', '=', $type_of_line)
                        ->union($mass_message_logs)
                        ->orderBy('created_at', 'DESC')
                        ->orderBy('id', 'DESC')
                        ->paginate(50, ['*'], 'google_review_reply_messages');
                }
            } else {
                $google_review_reply_messages = $google_review_reply_messages->union($mass_message_logs)->union($aw_sms)->union($aw_email)
                    ->orderBy('created_at', 'DESC')
                    ->orderBy('id', 'DESC')
                    ->paginate(50, ['*'], 'google_review_reply_messages');
            }


            $logTroubles = ClientAuthFailedSubmitLog::Where('tenant_id', $tenant_id)
                ->whereDate('created_at', '>=', $startDateLogTrouble)
                ->whereDate('created_at', '<=', $endDateLogTrouble)
                ->where('is_handled', false)
                ->whereNull('deleted_at')
                ->orderBy('id', 'DESC')
                ->paginate(50, ['*'], 'client_auth_failed_submit_logs');

            $notification_event_names = TenantNotificationLog::select('event_name')->distinct()->get();

            return $this->_loadContent('admin.pages.dashboard', ['notification_event_names' => $notification_event_names, 'logTroubles' => $logTroubles, 'startDateLogTrouble' => $startDateLogTrouble, 'endDateLogTrouble' => $endDateLogTrouble, 'logs' => $log, 'week_date' => $formatted_dates, 'feedbacks' => $feedbacks, 'google_review_reply_messages' => $google_review_reply_messages, 'start_date' => $startDate, 'end_date' => $endDate, 'start_date_log' => $startDateLog, 'end_date_log' => $endDateLog, 'start_date_msg' => $startDateMsg, 'end_date_msg' => $endDateMsg, 'type_of_line' => $type_of_line, 'login_status' => $login_status, 'client_name' => $client_name]);
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

    public function searchFeedBack(Request $request)
    {
        $searchValue = $request->searchvalue;
        $tenant_id = $this->cur_tenant_id;
        $startDate = request()->has('log_start_date') ? request()->get('log_start_date') : Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = request()->has('log_end_date') ? request()->get('log_end_date') : Carbon::now()->format('Y-m-d');
        $feedbacks = Feedbacks::Where('tenant_id', $tenant_id)
            ->where('project_name', 'LIKE', '%' . $searchValue . '%')
            ->where('client_name', 'LIKE', '%' . $searchValue . '%')
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->orderBy('id', 'DESC')
            ->get();
        $html = '';
        if (!$feedbacks->isEmpty()) {
            foreach ($feedbacks as $key => $single_feedback) {
                $html .= '<tr>';
                $html .= '<td>' . $single_feedback->client_name . '</td>';
                $html .= '<td>' . $single_feedback->project_name . '</td>';
                $html .= '<td>' . $single_feedback->project_id . '</td>';
                $html .= '<td>' . $single_feedback->legal_team_name . '</td>';
                $html .= '<td>' . $single_feedback->project_phase . '</td>';
                $html .= "<td>" . \Carbon\Carbon::parse($single_feedback->created_at)->format('m-d-Y') . "</td>";
                $html .= '<td><i class="fa fa-eye text-success" id="' . $key . '" onclick="get_feedback_data(this.id)" data-toggle="modal" data-target="#starRatingModal" style="cursor:pointer;"></i></td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr>';
            $html .= '<td>Data not found</td>';
            $html .= '</tr>';
        }
        echo $html;
        return;
    }

    /**
     * [GET] Dashboard Graph Data for Admin
     */
    public function get_graph_data(Request $request, $subdomain)
    {
        try {
            $get_day = $request->get('day');
            $get_total = $request->get('total');
            //$startDateLog = request()->has('log_start') ? request()->get('log_start') : Carbon::now()->startOfMonth()->format('Y-m-d');
            //$endDateLog = request()->has('log_end') ? request()->get('log_end') : Carbon::now()->format('Y-m-d');

            $startDateLog = Carbon::now()->subDays(6)->format('Y-m-d');
            $endDateLog = Carbon::now()->format('Y-m-d 23:59:59');

            $tenant_id = $this->cur_tenant_id;

            if ($get_day) {
                if ($get_day == 'week') {
                    $results = DB::select(DB::raw("SELECT * FROM log where tenant_id = $tenant_id and created_at >= DATE(NOW()) - INTERVAL 7 DAY GROUP BY Lookup_Name, Lookup_Phone_num, Lookup_Project_Id"));
                } elseif ($get_day == 'today') {
                    $results = DB::select(DB::raw("SELECT * FROM log where tenant_id = $tenant_id and created_at >= DATE(NOW()) GROUP BY Lookup_Name, Lookup_Phone_num, Lookup_Project_Id"));
                }
                echo count($results) ? count($results) : '0';
            } elseif ($get_total) {

                $return = array();
                $dateTimeEnd = new DateTime();
                $dateTimeStart = new DateTime();
                $return['max-day'] = $dateTimeEnd->getTimestamp() * 1000;
                $dateTimeStart->modify('-6 DAY');
                $return['min-day'] = $dateTimeStart->getTimestamp() * 1000;
                $return['month'] = $dateTimeStart->format('M');
                // interval for dates
                $step = DateInterval::createFromDateString('1 day');
                $dateTimeEnd->modify('1 DAY');
                $period = new DatePeriod($dateTimeStart, $step, $dateTimeEnd);
                //echo "SELECT date_format(created_at,'%d') as days ,count(Id) as total FROM log  Where tenant_id = $tenant_id and created_at >= '{$startDateLog}' and created_at <= '{$endDateLog}' GROUP BY date_format(Created_at, '%Y-%m-%d')";die;
                $results = DB::select(DB::raw("SELECT date_format(created_at,'%d') as days ,count(Id) as total FROM log  Where tenant_id = $tenant_id and created_at >= '{$startDateLog}' and created_at <= '{$endDateLog}' GROUP BY date_format(Created_at, '%Y-%m-%d')"));

                $results = json_decode(json_encode($results), true);

                $return['max-value'] = 5;
                // get max from results
                if (count($results) > 0) {
                    $totals = array_column($results, 'total');
                    $max = max($totals);
                    if ($max > $return['max-value']) {
                        $return['max-value'] = $max;
                    }
                }

                // get counts for each day
                $days = array_column($results, 'days');
                $totalArr = [];
                foreach ($period as $dt) {
                    $day = intval($dt->format('d'));
                    $isExist = array_search($day, $days);
                    if (gettype($isExist) == 'integer') {
                        $totalArr[$dt->getTimestamp() * 1000] = intval($results[$isExist]['total']);
                    } else {
                        $totalArr[$dt->getTimestamp() * 1000] = 0;
                    }
                }
                $return['data'] = $totalArr;
                $return = json_encode($return);
                echo $return;
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [GET] Dashbaord Table Data for Admin
     */
    public function get_table_data($subdomain, $value)
    {
        try {
            $tenant_id = $this->cur_tenant_id;
            if ($value == 'week') {
                $results = DB::select(DB::raw("SELECT * FROM log where tenant_id = $tenant_id and created_at >= DATE(NOW()) - INTERVAL 7 DAY ORDER BY id desc "));
            } else if ($value == 'month') {
                $results = Log::Where('tenant_id', $tenant_id)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->orderBy('id', 'DESC')
                    ->get();
            } elseif ($value == 'today') {
                $results = DB::select(DB::raw("SELECT * FROM log where tenant_id = $tenant_id and DATE(created_at) = DATE(NOW()) ORDER BY id desc "));
            }

            return json_encode($results);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [GET] Export Feedbacks CSV
     */

    public function exportFeedbacksCsv(Request $request)
    {
        $service = new ExportService();
        return $service->exportFeedbacksCsv($request, $this->cur_tenant_id);
    }

    /**
     * [POST] Edit Tenants Active/Inactive for Super Admin
     */
    public function acceptTerms(Request $request)
    {
        $data = $request->all();
        try {
            $tenant_id = $data['tenant_id'];
            $status = ($data['status'] == 'agree') ? 1 : 0;
            $values = array('is_accept_license' => $status);
            $tenant_details_update = DB::table('tenants')->where('id', $tenant_id)->update($values);

            // logout user if not accepted
            if (!$status) {
                Auth::logout();
            }

            return response()->json([
                'success'        => true
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success'        => true
            ], 200);
        }
    }

    /**
     * [GET] Export Usage Log CSV
     */
    public function exportUsageLogCsv(Request $request)
    {
        $service = new ExportService();
        return $service->exportUsageLogCsv($request, $this->cur_tenant_id);
    }

    /**
     * [GET] Export Message Log CSV
     */
    public function exportMessageLogCsv(Request $request)
    {
        $service = new ExportService();
        return $service->exportMessageLogCsv($request, $this->cur_tenant_id);
    }

    /**
     * [GET] Export Message Log CSV
     */
    public function getClientList(Request $request)
    {
        try {
            $tenant_id = $this->cur_tenant_id;
            $Tenant = Tenant::find($tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $filevine_api = new FilevineService($apiurl, "");
            $clients = [];


            /*   $search_value = $request->lookup_name;
            $offset = 0;
            $limit = 1000;
            do {
                $projects_object = json_decode($filevine_api->getProjectsList($limit, $offset), TRUE);
                $next_link = trim($projects_object['links']['next']);
                if (isset($projects_object['items'])) {
                    foreach ($projects_object['items'] as $project) {
                        if (stripos($project['clientName'], $search_value) !== false) {
                            $clients[] = [
                                'client_id' => $project['clientId']['native'],
                                'full_name' => $project['clientName'],
                                'project_name' => $project['projectName'],
                                'project_id' => $project['projectId']['native'],
                            ];
                        }
                    }
                }
                $offset += $limit;
            } while ($next_link);  */


            $lookup_first_name = $request->lookup_first_name;
            $lookup_last_name = $request->lookup_last_name;
            $clients_firstname = [];
            $clients_lastname = [];

            $params = [
                'limit' => 1000,
                'firstName' => $lookup_first_name,
            ];
            $contacts = json_decode($filevine_api->getContacts($params), TRUE);
            if (isset($contacts['items'])) {
                foreach ($contacts['items'] as $contact) {
                    $clients_firstname[] = [
                        'client_id' => $contact['personId']['native'],
                        'full_name' => $contact['fullName'],
                        'primaryEmail' => isset($contact['primaryEmail']) ? $contact['primaryEmail'] : ''
                    ];
                }
            }


            $params = [
                'limit' => 1000,
                'lastName' => $lookup_last_name
            ];
            $contacts = json_decode($filevine_api->getContacts($params), TRUE);
            if (isset($contacts['items'])) {
                foreach ($contacts['items'] as $contact) {
                    $clients_lastname[] = [
                        'client_id' => $contact['personId']['native'],
                        'full_name' => $contact['fullName'],
                        'primaryEmail' => isset($contact['primaryEmail']) ? $contact['primaryEmail'] : ''
                    ];
                }
            }

            $clients = array_merge($clients_firstname, $clients_lastname);

            return response()->json([
                'status' => true,
                'data' => $clients
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [POST] Send Client Info
     */
    public function sendClientInfo(Request $request)
    {
        try {
            $client_id = $request->client_id;
            $log_id = $request->log_id;
            $tenant_id = $this->cur_tenant_id;
            $Tenant = Tenant::find($tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $filevine_api = new FilevineService($apiurl, "");

            $contact = json_decode($filevine_api->getContactByContactId($client_id));
            if (!empty($contact)) {
                $contact_phones = $contact->phones;
                $phones = "";
                foreach ($contact_phones as $phone) {
                    $phones .= $phone->rawNumber . ",";
                }
                $dynamic_data = [
                    'full_name' => $contact->fullName,
                    'lookup_first_name' => $contact->firstName,
                    'lookup_last_name' => $contact->lastName,
                    'lookup_phone' => strlen($phones) ? rtrim($phones, ",") : $phones,
                    'lookup_email' => $contact->primaryEmail,
                ];
                $this->sendGridServices->sendAdminHandlerNoteEmail($contact->primaryEmail, $dynamic_data);
                ClientAuthFailedSubmitLog::where(['id' => $log_id])
                    ->update([
                        'is_handled' => true,
                        'handled_action' => 'Mail',
                        'at_sent_client_note' => date('Y-m-d H:i:s'),
                        'matched_client_info' => json_encode($contact)
                    ]);
            }

            return response()->json([
                'status' => true,
                'message' => "Client Information Sent Successfully!"
            ]);
        } catch (Exception $e) {
            Logging::warning($e->getMessage());
            return $e->getMessage();
        }
    }

    /**
     * [POST] Add Client Into Block List
     */
    public function addClientIntoBlock(Request $request)
    {
        try {
            $client_id = $request->client_id;
            $log_id = $request->log_id;
            $tenant_id = $this->cur_tenant_id;
            $Tenant = Tenant::find($tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $filevine_api = new FilevineService($apiurl, "");

            $contact = json_decode($filevine_api->getContactByContactId($client_id));
            if (!empty($contact)) {
                Blacklist::create([
                    'tenant_id' => $this->cur_tenant_id,
                    'fv_full_name' => $contact->fullName,
                    'fv_client_id' => $client_id,
                ]);
                ClientAuthFailedSubmitLog::where(['id' => $log_id])
                    ->update([
                        'is_handled' => true,
                        'handled_action' => 'Block',
                        'at_added_black_list' => date('Y-m-d H:i:s'),
                        'matched_client_info' => json_encode($contact)
                    ]);
            }

            return response()->json([
                'status' => true,
                'message' => "Successfully Blocked!"
            ]);
        } catch (Exception $e) {
            Logging::warning($e->getMessage());
            return $e->getMessage();
        }
    }


    /**
     * [POST] Update Client Info
     */
    public function updateClientInfo(Request $request)
    {
        try {
            $client_id = $request->client_id;
            $log_id = $request->log_id;
            $tenant_id = $this->cur_tenant_id;
            $Tenant = Tenant::find($tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $filevine_api = new FilevineService($apiurl, "");

            if ($client_id) {
                $contact = $org_contact = json_decode($filevine_api->getContactByContactId($client_id), TRUE);
                if (isset($contact['personId']['native'])) {

                    $log_note = "";

                    $log = ClientAuthFailedSubmitLog::find($log_id);
                    if (!empty($log->lookup_first_name)) {
                        $contact['firstName'] = $log->lookup_first_name;
                        $log_note .= "First Name:" . $log->lookup_first_name;
                    }
                    if (!empty($log->lookup_last_name)) {
                        $contact['lastName'] = $log->lookup_last_name;
                        $log_note .= ", Last Name:" . $log->lookup_last_name;
                    }
                    if (!empty($log->lookup_phone)) {
                        $contact['phones'][0]['rawNumber'] = $log->lookup_phone;
                        $contact['phones'][0]['number'] = $log->lookup_phone;
                        $log_note .= ", Phone:" . $log->lookup_phone;
                    }

                    if (!empty($log->lookup_email)) {
                        $contact['emails'][0]['address'] = $log->lookup_email;
                        $contact['primaryEmail'] = $log->lookup_email;
                        $log_note .= ", Email:" . $log->lookup_email;
                    }

                    $contactupdate = json_decode($filevine_api->updateContact($client_id, $contact));

                    ClientAuthFailedSubmitLog::where(['id' => $log_id])
                        ->update([
                            'is_handled' => true,
                            'handled_action' => 'Update Contact',
                            'at_update_client' => date('Y-m-d H:i:s'),
                            'matched_client_info' => json_encode($org_contact)
                        ]);

                    // Update log table
                    Log::where('client_auth_failed_submit_log_id', $log_id)->update([
                        'note' => $log_note
                    ]);

                    // Send an SMS to Client Phone
                    if (env('APP_ENV') == 'production') {
                        $tenant_details = Tenant::where('id', $tenant_id)->first();
                        $msg_content = "Hello " . $log->lookup_first_name . ", this is " . $tenant_details->tenant_law_firm_name . ". Your updated contact information has been accepted. You may now attempt to login to your client portal at " . "https://" . $tenant_details->tenant_name . ".vinetegrate.com";
                        $twilio_api = new TwilioService();
                        $sms_number_from = env('TWILIO_FROM');
                        $msgresponse = $twilio_api->send_sms_message($log->lookup_phone, $sms_number_from, $msg_content);

                        $msg_obj = ['Sent SMS From Dashboard to: ' . $log->lookup_phone, $msg_content];
                        Logging::warning(json_encode($msg_obj));
                    }
                }
            }

            return response()->json([
                'status' => true,
                'message' => "Successfully Updated Client Info!"
            ]);
        } catch (Exception $e) {
            Logging::warning($e->getMessage());
            return $e->getMessage();
        }
    }


    /**
     * [GET] Export Submitted Auth Log CSV
     */
    public function exportSubmittedLogCsv(Request $request)
    {
        $service = new ExportService();
        return $service->exportSubmittedLogCsv($request, $this->cur_tenant_id);
    }

    /**
     * [GET] Get Tenant Admin Notification Log
     */
    public function getNotificationLog(Request $request)
    {
        $log_start_date = $request->log_start_date;
        $log_end_date = $request->log_end_date;
        $notification_event_name = $request->notification_event_name;
        $tenant_id = $this->cur_tenant_id;

        $logs = TenantNotificationLog::where('tenant_id', $tenant_id)
            ->where('created_at', '>=', $log_start_date)
            ->where('created_at', '<=', $log_end_date . ' 23:59:59');
        if (!empty($notification_event_name)) {
            $logs = $logs->where('event_name', '=', $notification_event_name);
        }
        $logs = $logs->get();

        return response()->json([
            'data' => $logs,
            'status' => true,
        ]);
    }

    /**
     * [GET] Export Tenant Admin Notification Log
     */
    public function exportNotificationLog(Request $request)
    {
        $service = new ExportService();
        return $service->exportNotificationLogCsv($request, $this->cur_tenant_id);
    }

    /**
     *  [POST] Delete a Failed Submit Log
     */
    public function deleteFailedSubmitLog(Request $request)
    {
        try {
            $log_id = $request->input('log_id');
            ClientAuthFailedSubmitLog::where('id', $log_id)->where('tenant_id', $this->cur_tenant_id)->delete();

            return response()->json([
                'status'  => true,
                'message' => "Failed login information deleted successfully!"
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
