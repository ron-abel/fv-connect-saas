<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Imports\AutoNoteGoogleReviewLinksImport;
use App\Models\AutoNoteGoogleReview;
use App\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as Logging;
use Carbon\Carbon;

use App\Models\Tenant;
use Exception;
use App\Models\AutoNoteOccurrences;
use App\Models\AutoNotePhases;
use App\Models\AutoNoteGoogleReviewLinks;
use App\Models\AutoNoteGoogleReviewCities;
use App\Models\Variable;

use App\Services\FilevineService;
use Illuminate\Support\Str;
use Maatwebsite\Excel\HeadingRowImport;

class AutomatedCommunicationsController extends Controller
{
    public $cur_tenant_id;
    public function __construct()
    {
        Controller::setSubDomainName();
        $this->cur_tenant_id = session()->get('tenant_id');
    }

    /**
     * [GET] Automated Communications page for Admin
     */

    public function index(Request $request)
    {
        try {

            $tenant_id = $this->cur_tenant_id;
            $Tenant = Tenant::find($tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }

            $auto_note_details = AutoNoteOccurrences::where([
                'tenant_id' => $tenant_id,
            ])->first();

            $config_details = DB::table('config')->where('tenant_id', $tenant_id)->first();
            $current_project_typeid = "";
            // get phase categories if added any
            $mappings = DB::select(DB::raw("
                            select pm.*, t.`template_name`
                            from phase_mappings as pm
                            INNER JOIN phase_categories AS pc ON pc.`id` = pm.`Phase_category_Id`
                            INNER JOIN templates AS t ON t.`Id` = pc.`template_id`
                            where pm.tenant_id = $tenant_id
                            group by Project_Type_Id
                            "));

            $obj = new FilevineService($apiurl, "");

            // get project types
            $mappings = [];
            $fv_project_type_list = $obj->getProjectTypeList();
            if ($fv_project_type_list != null) {
                $fv_project_type_list = json_decode($fv_project_type_list, true);

                if (isset($fv_project_type_list['count']) && $fv_project_type_list['count'] > 0) {
                    foreach ($fv_project_type_list['items'] as $key => $item) {
                        $mappings[] = (object)array(
                            "project_type_id" => $item['projectTypeId']['native'],
                            "project_type_name" => $item['name']
                        );
                    }
                }
            }

            $auto_note_phases = [];
            $auto_note_phases_Array = [];
            $project_type_phases = [];
            $current_project_typeid = "";
            if (count($mappings) > 0 && isset($mappings[0]->project_type_id)) {
                $current_project_typeid = $mappings[0]->project_type_id;

                // for tab selection
                $phase_change_project_typeid = session('phase_change_project_typeid');
                if (!empty($phase_change_project_typeid)) {
                    $current_project_typeid = $phase_change_project_typeid;
                }

                // get all project types phases
                $Tenant = Tenant::find($tenant_id);
                $apiurl = config('services.fv.default_api_base_url');
                if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                    $apiurl = $Tenant->fv_api_base_url;
                }
                $filevine_api = new FilevineService($apiurl, "");

                $fv_project_type_phases = json_decode($filevine_api->getProjectTypePhaseList($current_project_typeid), true);
                if (isset($fv_project_type_phases['items'])) {
                    $project_type_phases = $fv_project_type_phases['items'];
                }
                $auto_note_phases = AutoNotePhases::where([
                    'tenant_id' => $tenant_id,
                    'fv_project_type_id' => $current_project_typeid
                ])->get();

                if (isset($auto_note_phases) and !empty($auto_note_phases)) {
                    $auto_note_phases_Array = $auto_note_phases->pluck('phase_name')->toArray();
                }
            }

            $variable_keys = Variable::getVariableKeyByPage('is_phase_change_sms');

            return $this->_loadContent("admin.pages.automated_communications", ['variable_keys' => $variable_keys, 'current_project_typeid' => $current_project_typeid, 'auto_note_details' => $auto_note_details, 'auto_note_phases' => $auto_note_phases, 'project_type_phases' => $project_type_phases, 'mappings' => $mappings, 'config_details' => $config_details, 'auto_note_phases_Array' => $auto_note_phases_Array]);
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

    public function getProjectTypePhaseLists(Request $request)
    {
        try {
            session()->put('phase_change_project_typeid',  $request->type_id);
            $tenant_id = $this->cur_tenant_id;
            $load = $request->get("load");
            if ($load == "data_type") {

                $auto_note_details = AutoNoteOccurrences::where([
                    'tenant_id' => $tenant_id,
                ])->first();
                $current_project_typeid = $request->type_id;

                // get all project types phases
                $Tenant = Tenant::find($tenant_id);
                $apiurl = config('services.fv.default_api_base_url');
                if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                    $apiurl = $Tenant->fv_api_base_url;
                }
                $filevine_api = new FilevineService($apiurl, "");
                $project_type_phases = [];
                $fv_project_type_phases = json_decode($filevine_api->getProjectTypePhaseList($current_project_typeid), true);
                if (isset($fv_project_type_phases['items'])) {
                    $project_type_phases = $fv_project_type_phases['items'];
                }
                $auto_note_phases = AutoNotePhases::where([
                    'tenant_id' => $tenant_id,
                    'fv_project_type_id' => $current_project_typeid
                ])->get();

                $auto_note_phases_Array = [];
                if (isset($auto_note_phases) and !empty($auto_note_phases)) {
                    $auto_note_phases_Array = $auto_note_phases->pluck('phase_name')->toArray();
                }

                return $this->_loadContent("admin.pages.automated_communications_ajax", ['current_project_typeid' => $current_project_typeid, 'auto_note_details' => $auto_note_details, 'auto_note_phases' => $auto_note_phases, 'project_type_phases' => $project_type_phases, 'auto_note_phases_Array' => $auto_note_phases_Array]);
            } elseif ($load == "add_new_row") {
                $auto_note_details = AutoNoteOccurrences::where([
                    'tenant_id' => $tenant_id,
                ])->first();
                $current_project_typeid = $request->type_id;

                // get all project types phases
                $Tenant = Tenant::find($tenant_id);
                $apiurl = config('services.fv.default_api_base_url');
                if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                    $apiurl = $Tenant->fv_api_base_url;
                }
                $filevine_api = new FilevineService($apiurl, "");
                $project_type_phases = [];
                $fv_project_type_phases = json_decode($filevine_api->getProjectTypePhaseList($current_project_typeid), true);
                if (isset($fv_project_type_phases['items'])) {
                    $project_type_phases = $fv_project_type_phases['items'];
                }
                $auto_note_phases = AutoNotePhases::where([
                    'tenant_id' => $tenant_id,
                    'fv_project_type_id' => $current_project_typeid
                ])->get();
                $auto_note_phases_Array = [];
                if (isset($auto_note_phases) and !empty($auto_note_phases)) {
                    $auto_note_phases_Array = $auto_note_phases->pluck('phase_name')->toArray();
                }

                $project_type_phases_get = [];
                foreach ($project_type_phases as $phase) {
                    if (!in_array($phase['name'], $auto_note_phases_Array)) {
                        $project_type_phases_get[] = $phase;
                    }
                }
                $project_type_phases = $project_type_phases_get;
                $html = '<select name="fv_phase_id" onchange="changeHiddenPhase(this)" class="form-control fv_phase_id" required>';
                $html .= '<option value="" selected="selected">--Select Phase--</option>';
                foreach ($project_type_phases as $phase) {
                    $html .= '<option  value="' . $phase['phaseId']['native'] . '">' . $phase['name'] . '</option>';
                }
                $html .= "</select>";
                echo $html;
                die;
            } else {

                $auto_note_details = AutoNoteOccurrences::where([
                    'tenant_id' => $tenant_id,
                ])->first();

                $auto_note_phases = AutoNotePhases::where([
                    'tenant_id' => $tenant_id,
                ])->get();

                $Tenant = Tenant::find($tenant_id);

                $current_project_typeid = "";
                // get phase categories if added any
                $mappings = DB::select(DB::raw("
                                select pm.*, t.`template_name`
                                from phase_mappings as pm
                                INNER JOIN phase_categories AS pc ON pc.`id` = pm.`Phase_category_Id`
                                INNER JOIN templates AS t ON t.`Id` = pc.`template_id`
                                where pm.tenant_id = $tenant_id
                                group by Project_Type_Id
                                "));

                $project_type_phases = [];
                if (count($mappings) > 0) {
                    $current_project_typeid = $request->type_id;

                    // get all project types phases
                    $apiurl = config('services.fv.default_api_base_url');
                    if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                        $apiurl = $Tenant->fv_api_base_url;
                    }
                    $filevine_api = new FilevineService($apiurl, "");

                    $fv_project_type_phases = json_decode($filevine_api->getProjectTypePhaseList($current_project_typeid), true);

                    if (isset($fv_project_type_phases['items'])) {
                        $project_type_phases = $fv_project_type_phases['items'];
                        return $project_type_phases;
                    }
                }

                // empty phases.
                return [];
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function saveSmsTimeBuffer(Request $request)
    {
        $data = $this->validate($request, [
            'sms_buffer_time' => 'sometimes|numeric|min:300|max:3600',
        ]);
        $sms_buffer_time = isset($data['sms_buffer_time']) ? $data['sms_buffer_time'] : 0;
        $is_sms_buffer_time_enabled = 0;
        if (isset($request->enable_sms_buffer_time) && $request->enable_sms_buffer_time == 'enable') {
            $is_sms_buffer_time_enabled = 1;
        }

        $tenant_id = $this->cur_tenant_id;
        $configs = DB::table('config')->where('tenant_id', $tenant_id)->first();
        if ($configs == null) {
            $values = array(
                'tenant_id' => $tenant_id,
                'sms_buffer_time' => env('SMS_NOTE_BUFFER_DEFAULT_TIME', '300'),
                'is_sms_buffer_time_enabled' => 1
            );
            DB::table('config')->insert($values);
        } else {
            DB::table('config')->where('tenant_id', $tenant_id)->update([
                'sms_buffer_time' => $sms_buffer_time,
                'is_sms_buffer_time_enabled' => $is_sms_buffer_time_enabled
            ]);
        }

        if ($request->ontoggle) {
            return true;
        } else {
            return back()->with('success', 'Setting saved successfully!');
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function saveAutoNoteGoogleReview(Request $request)
    {
        try {
            $tenant_id = $this->cur_tenant_id;
            $this->validate($request, [
                'minimum_score' => 'sometimes|nullable|numeric|min:0|max:5',
                'review_request_text_body' => 'required'
            ]);
            $data = $request->only('review_request_text_body');
            $data['is_set_qualified_response_threshold'] = 0;
            $data['is_send_unqualified_response_request'] = 0;

            if ($request->has('is_set_qualified_response_threshold')) {
                $data['is_set_qualified_response_threshold'] = 1;
                $data['minimum_score'] = $request->minimum_score;
                $data['qualified_review_request_msg_body'] = $request->qualified_review_request_msg_body;
                if ($request->has('is_send_unqualified_response_request')) {
                    $data['is_send_unqualified_response_request'] = 1;
                    $data['unqualified_review_request_msg_body'] = $request->unqualified_review_request_msg_body;
                }
            }

            $auto_note_google_review = AutoNoteGoogleReview::updateOrCreate(
                ['tenant_id' => $tenant_id],
                $data
            );

            return back()->with('success', 'Setting saved successfully!');
        } catch (\Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * [GET] Google Review Automated Communications page for Admin
     */

    public function googleReviewAutomatedCommunications(Request $request)
    {
        try {

            $tenant_id = $this->cur_tenant_id;

            $auto_note_details = AutoNoteOccurrences::where([
                'tenant_id' => $tenant_id,
            ])->first();

            $google_reviews_notes = DB::table('auto_note_google_review_links')
                ->join('auto_note_google_review_cities', 'auto_note_google_review_links.id', '=', 'auto_note_google_review_cities.auto_note_google_review_link_id')
                ->select('auto_note_google_review_links.*', 'auto_note_google_review_cities.auto_note_google_review_link_id', 'auto_note_google_review_cities.zip_code')
                ->where('auto_note_google_review_links.tenant_id', '=', $tenant_id)
                ->get();

            $auto_note_google_review = AutoNoteGoogleReview::where([
                'tenant_id' => $tenant_id,
            ])->first();

            $google_reviews_links = AutoNoteGoogleReviewLinks::where([
                'tenant_id' => $tenant_id,
            ])->get();

            $google_reviews_cities = array();

            foreach ($google_reviews_links as $single_google_links) {
                $google_reviews_cities_data = AutoNoteGoogleReviewCities::where([
                    'auto_note_google_review_link_id' => $single_google_links->id,
                ])->get();

                $google_reviews_cities[$single_google_links->id] = $google_reviews_cities_data;
            }

            $startDateMsg = request()->has('msg_start_date') ? date("Y-m-d", strtotime(request()->get('msg_start_date'))) : Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDateMsg = request()->has('msg_end_date') ? date("Y-m-d", strtotime(request()->get('msg_end_date'))) : Carbon::now()->format('Y-m-d');

            $google_review_reply_messages = DB::table('auto_note_google_review_messages')
                ->join('fv_clients', 'auto_note_google_review_messages.client_id', '=', 'fv_clients.id')
                ->select('auto_note_google_review_messages.*', 'fv_clients.fv_client_name')
                ->where(['auto_note_google_review_messages.tenant_id' => $tenant_id])
                ->where(['auto_note_google_review_messages.msg_type' => 'in'])
                ->where('auto_note_google_review_messages.created_at', '>=', $startDateMsg)
                ->where('auto_note_google_review_messages.created_at', '<=', $endDateMsg . ' 23:59:59')
                ->orderBy('auto_note_google_review_messages.id', 'DESC')
                ->paginate(50, ['*'], 'google_review_reply_messages');

            foreach ($google_review_reply_messages as $google_review) {
                $score = (int) filter_var($google_review->message_body, FILTER_SANITIZE_NUMBER_INT);
                $google_review->score = $score ? $score : '';
            }

            $variable_keys = Variable::getVariableKeyByPage('is_review_request_sms');

            return $this->_loadContent("admin.pages.google_review_automated_communications", ['variable_keys' => $variable_keys, 'auto_note_details' => $auto_note_details, 'auto_note_google_review' => $auto_note_google_review, 'google_reviews_links' => $google_reviews_links, 'google_reviews_cities' =>  $google_reviews_cities, 'google_review_reply_messages' => $google_review_reply_messages, 'start_date_msg' => $startDateMsg, 'end_date_msg' => $endDateMsg]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function googleReviewAutomatedCommunicationsUploadCsv(Request $request)
    {
        try {
            $tenant_id = $this->cur_tenant_id;
            $data = $this->validate($request, [
                'google_review_file' => 'required|mimes:csv,txt',
            ]);

            $localFilePath = request()->file('google_review_file')->storeAs('uploads', md5(strtotime('now')) . '.csv', 'local');

            // Read first line and check if all required fields are there or not
            $ext = pathinfo($localFilePath, PATHINFO_EXTENSION);
            $headings = (new HeadingRowImport)->toArray($localFilePath, null, ($ext == 'txt' ? \Maatwebsite\Excel\Excel::CSV : null));
            $requiredFields = [
                'Zip Code', 'Review Link', 'Description', 'Default'
            ];

            $sluggableFields = array_map(function ($i) {
                return Str::slug($i, '_');
            }, $requiredFields);
            $requiredFields = array_combine($sluggableFields, $requiredFields);

            if (count($requiredFields) != count(array_intersect($headings[0][0], $sluggableFields))) {
                return back()->with('error', 'Please check the file format. Required fields are zip_code, review link, description, default');
            }

            $errors = [];
            $importObj = new AutoNoteGoogleReviewLinksImport($this->cur_tenant_id);
            try {
                $importObj->import($localFilePath);

                $failures = $importObj->failures();
            } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                $failures = $e->failures();
            }

            $lines = [];

            if ($importObj->getTotalDuplicate()) {
                $lines[] = "Duplicated Zipcodes : " . implode(", ", $importObj->getTotalDuplicateZips());
            }

            if (!empty($failures)) {
                foreach ($failures as $failure) {
                    $errors[] = [
                        $failure->row(),
                        $failure->attribute(),
                        $failure->errors(),
                        $failure->values(),
                    ];
                }

                foreach ($errors as $err) {
                    $lines[] = "Error in Row # {$err[0]} Column \"" . $requiredFields[$err[1]] . "\" => " . implode(', ', $err[2]);
                }
            }


            @unlink($localFilePath);

            if (!empty($lines)) {
                return back()->with('error', implode('<br>', $lines))
                    ->with('success', number_format($importObj->getTotalImported()) . ' records imported successfully');
            } else {
                return back()->with('success', number_format($importObj->getTotalImported()) . ' records imported successfully');
            }
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * [POST] Update Auto Note Status
     */

    public function updateAutoNotesOccurenceStatus(Request $request)
    {
        try {
            $tenant_id = $this->cur_tenant_id;
            $Tenant = Tenant::find($tenant_id);
            $auto_note_exist = AutoNoteOccurrences::where([
                'tenant_id' => $tenant_id,
            ])->first();

            $current_date = date('Y-m-d H:i:s');

            if ($request->action_name == 'is_on') {

                if ($request->value == 'on') {
                    $is_on = 1;
                } else {
                    $is_on = 0;
                }

                if ($auto_note_exist) {
                    $values = array('is_on' => $is_on, 'updated_at' => $current_date);
                    $auto_note_update = AutoNoteOccurrences::where('tenant_id', $tenant_id)->update($values);

                    $response = ['success' => true, 'message' => 'Setting saved successfully!'];
                } else {
                    $values = array('tenant_id' => $tenant_id, 'is_on' => $is_on, 'is_live' => 0, 'google_review_is_on' => 0, 'google_review_is_live' => 0, 'created_at' => $current_date, 'updated_at' => $current_date);
                    $auto_note_created = AutoNoteOccurrences::create($values);

                    $response = ['success' => true, 'message' => 'Setting saved successfully!'];
                }
            } elseif ($request->action_name == 'is_live') {

                if ($request->value == 'go_live') {
                    $apiurl = config('services.fv.default_api_base_url');
                    if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                        $apiurl = $Tenant->fv_api_base_url;
                    }
                    $obj = new FilevineService($apiurl, "");

                    $eventIds = "Project.PhaseChanged";
                    $get_all_subscriptions = $obj->getSubscriptionsList();
                    $get_all_subscriptions = json_decode($get_all_subscriptions);
                    $url = url('api/v1/webhook/send_notification_phase_changed');
                    $type = 'PhaseChanged';

                    $filevine_exist = "";
                    foreach ($get_all_subscriptions as $single_subscription) {
                        if (isset($single_subscription->eventIds, $single_subscription->endpoint) && $single_subscription->eventIds == [$eventIds] && $single_subscription->endpoint == $url) {
                            $filevine_exist = "AlreadyExists";
                        }
                    }

                    if (!isset($get_all_subscriptions->error) && $filevine_exist != 'AlreadyExists') {
                        $filevine_hook = $obj->createSubscription($type, $url, $eventIds);
                        Logging::warning(json_encode($filevine_hook));
                    }

                    $is_live = 1;
                    $configs = DB::table('config')->where('tenant_id', $tenant_id)->first();
                    if ($configs == null) {
                        $values = array('tenant_id' => $tenant_id, 'sms_buffer_time' => env('SMS_NOTE_BUFFER_DEFAULT_TIME', '300'));
                        DB::table('config')->insert($values);
                    } else {
                        DB::table('config')->where('tenant_id', $tenant_id)->update([
                            'sms_buffer_time' => env('SMS_NOTE_BUFFER_DEFAULT_TIME', '300'),
                        ]);
                    }
                } else {
                    $is_live = 0;
                }

                if ($auto_note_exist) {
                    $values = array('is_live' => $is_live, 'updated_at' => $current_date);
                    $auto_note_update = AutoNoteOccurrences::where('tenant_id', $tenant_id)->update($values);

                    $response = ['success' => true, 'message' => 'Setting saved successfully!'];
                } else {
                    $values = array('tenant_id' => $tenant_id, 'is_on' => 0, 'is_live' => $is_live, 'google_review_is_on' => 0, 'google_review_is_live' => 0, 'created_at' => $current_date, 'updated_at' => $current_date);
                    $auto_note_created = AutoNoteOccurrences::create($values);

                    $response = ['success' => true, 'message' => 'Setting saved successfully!'];
                }
            } elseif ($request->action_name == 'google_review_is_on') {
                if ($request->value == 'on') {
                    $google_review_is_on = 1;
                } else {
                    $google_review_is_on = 0;
                }

                if ($auto_note_exist) {
                    $values = array('google_review_is_on' => $google_review_is_on, 'updated_at' => $current_date);
                    $auto_note_update = AutoNoteOccurrences::where('tenant_id', $tenant_id)->update($values);

                    $response = ['success' => true, 'message' => 'Setting saved successfully!'];
                } else {
                    $values = array('tenant_id' => $tenant_id, 'is_on' => 0, 'is_live' => 0, 'google_review_is_on' => $google_review_is_on, 'google_review_is_live' => 0, 'created_at' => $current_date, 'updated_at' => $current_date);
                    $auto_note_created = AutoNoteOccurrences::create($values);

                    $response = ['success' => true, 'message' => 'Setting saved successfully!'];
                }
            } elseif ($request->action_name == 'google_review_is_live') {
                if ($request->value == 'go_live') {

                    $apiurl = config('services.fv.default_api_base_url');
                    if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                        $apiurl = $Tenant->fv_api_base_url;
                    }
                    $obj = new FilevineService($apiurl, "");
                    $eventIds = "Project.PhaseChanged";
                    $get_all_subscriptions = $obj->getSubscriptionsList();
                    $get_all_subscriptions = json_decode($get_all_subscriptions);
                    $url = url('api/v1/webhook/send_notification_phase_changed');
                    $type = 'PhaseChanged';

                    $filevine_exist = "";
                    foreach ($get_all_subscriptions as $single_subscription) {
                        if (isset($single_subscription->eventIds, $single_subscription->endpoint) && $single_subscription->eventIds == [$eventIds] && $single_subscription->endpoint == $url) {
                            $filevine_exist = "AlreadyExists";
                        }
                    }

                    if (!isset($get_all_subscriptions->error) && $filevine_exist != 'AlreadyExists') {
                        $filevine_hook = $obj->createSubscription($type, $url, $eventIds);
                        Logging::warning(json_encode($filevine_hook));
                    }

                    $google_review_is_live = 1;
                } else {
                    $google_review_is_live = 0;
                }

                if ($auto_note_exist) {
                    $values = array('google_review_is_live' => $google_review_is_live, 'updated_at' => $current_date);
                    $auto_note_update = AutoNoteOccurrences::where('tenant_id', $tenant_id)->update($values);

                    $response = ['success' => true, 'message' => 'Setting saved successfully!'];
                } else {
                    $values = array('tenant_id' => $tenant_id, 'is_on' => 0, 'is_live' => 0, 'google_review_is_on' => 0, 'google_review_is_live' => $google_review_is_live, 'created_at' => $current_date, 'updated_at' => $current_date);
                    $auto_note_created = AutoNoteOccurrences::create($values);

                    $response = ['success' => true, 'message' => 'Setting saved successfully!'];
                }
            }

            return json_encode($response);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [POST] Process Auto Note Phase Settings
     */

    public function processAutoNotePhaseSettings(Request $request)
    {
        try {
            $tenant_id = $this->cur_tenant_id;

            $note_phase_id = $request->id;
            $current_date = date('Y-m-d H:i:s');

            if (!empty($request->phase_change_type)) {

                $is_new = $request->is_new;
                $phase_change_type = $request->phase_change_type;
                $project_type_name = $request->project_type_name;
                $phase_change_event = $request->phase_change_event;
                $fv_phase_id = $request->fv_phase_id;
                $phase_change_enable = $request->phase_change_enable;
                $custom_message = $request->custom_message;
                $is_send_google_review = $request->is_send_google_review;

                $is_exist_phase_changed = AutoNotePhases::where('id', $note_phase_id)->get();

                if ($is_new == 1 && count($is_exist_phase_changed) <= 0) {

                    $values = array(
                        'tenant_id' => $tenant_id,
                        'fv_project_type_id' => $phase_change_type,
                        'fv_project_type_name' => $project_type_name,
                        'phase_name' => $phase_change_event,
                        'fv_phase_id' => $fv_phase_id,
                        'custom_message' => $custom_message,
                        'is_send_google_review' => $is_send_google_review,
                        'is_active' => $phase_change_enable,
                        'created_at' => $current_date,
                        'updated_at' => $current_date
                    );
                    $AutoNotePhasesExist = AutoNotePhases::where('fv_project_type_id', $phase_change_type)->where('fv_phase_id', $fv_phase_id)->where('tenant_id', $tenant_id)->first();
                    if (empty($AutoNotePhasesExist)) {
                        $new_auto_note = AutoNotePhases::create($values);
                        $response['success'] = true;
                        $response['message'] = 'Setting saved successfully!';
                        $response['new_data'] = $new_auto_note;
                    } else {
                        $response['success'] = false;
                        $response['message'] = 'Auto Note Already Exists';
                    }
                } else {
                    $values = array(
                        'fv_project_type_id' => $phase_change_type,
                        'fv_project_type_name' => $project_type_name,
                        'phase_name' => $phase_change_event,
                        'fv_phase_id' => $fv_phase_id,
                        'custom_message' => $custom_message,
                        'is_send_google_review' => $is_send_google_review,
                        'is_active' => $phase_change_enable,
                        'updated_at' => $current_date
                    );
                    $AutoNotePhasesExist = AutoNotePhases::where('id', '<>', $note_phase_id)->where('fv_project_type_id', $phase_change_type)->where('fv_phase_id', $fv_phase_id)->where('tenant_id', $tenant_id)->first();
                    if (empty($AutoNotePhasesExist)) {
                        AutoNotePhases::where('id', $note_phase_id)->update($values);
                        $response['success'] = true;
                        $response['message'] = 'Setting saved successfully!';
                    } else {
                        $response['success'] = false;
                        $response['message'] = 'Auto Note Already Exists';
                    }
                }
            } else if ($request->delete_action) {
                $response = ['success' => false, 'message' => ''];

                if ($request->delete_action == 'delete_action') {
                    AutoNotePhases::where('id', $note_phase_id)->delete();
                    $response['success'] = true;
                    $response['message'] = 'Setting saved successfully!';
                }
            }

            return json_encode($response);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [POST] Process Auto Note Phase Settings all save
     */


    public function processAutoNotePhaseSettingsSaveAll(Request $request)
    {
        $success = 0;
        $error = 0;
        foreach ($request->get('formData') as $formdata) {
            try {
                $tenant_id = $this->cur_tenant_id;
                $note_phase_id = $formdata['id'];
                $current_date = date('Y-m-d H:i:s');
                if (!empty($formdata['phase_change_type'])) {
                    $is_new = $formdata['is_new'];
                    $phase_change_type = $formdata['phase_change_type'];
                    session()->put('phase_change_project_typeid',  $phase_change_type);
                    $project_type_name = $formdata['project_type_name'];
                    $phase_change_event = $formdata['phase_change_event'];
                    $fv_phase_id = $formdata['fv_phase_id'];
                    $phase_change_enable = $formdata['phase_change_enable'];
                    $custom_message = $formdata['custom_message'];
                    $is_send_google_review = $formdata['is_send_google_review'];

                    $is_exist_phase_changed = AutoNotePhases::where('id', $note_phase_id)->get();

                    if ($is_new == 1 && count($is_exist_phase_changed) <= 0) {

                        $values = array(
                            'tenant_id' => $tenant_id,
                            'fv_project_type_id' => $phase_change_type,
                            'fv_project_type_name' => $project_type_name,
                            'phase_name' => $phase_change_event,
                            'fv_phase_id' => $fv_phase_id,
                            'custom_message' => $custom_message,
                            'is_send_google_review' => $is_send_google_review,
                            'is_active' => $phase_change_enable,
                            'created_at' => $current_date,
                            'updated_at' => $current_date
                        );
                        $AutoNotePhasesExist = AutoNotePhases::where('fv_project_type_id', $phase_change_type)->where('fv_phase_id', $fv_phase_id)->where('tenant_id', $tenant_id)->first();
                        if (empty($AutoNotePhasesExist)) {
                            $new_auto_note = AutoNotePhases::create($values);
                            $success++;
                            $response['new_data'] = $new_auto_note;
                        } else {
                            $error++;
                        }
                    } else {
                        $values = array(
                            'fv_project_type_id' => $phase_change_type,
                            'fv_project_type_name' => $project_type_name,
                            'phase_name' => $phase_change_event,
                            'fv_phase_id' => $fv_phase_id,
                            'custom_message' => $custom_message,
                            'is_send_google_review' => $is_send_google_review,
                            'is_active' => $phase_change_enable,
                            'updated_at' => $current_date
                        );
                        $AutoNotePhasesExist = AutoNotePhases::where('id', '<>', $note_phase_id)->where('fv_project_type_id', $phase_change_type)->where('fv_phase_id', $fv_phase_id)->where('tenant_id', $tenant_id)->first();
                        if (empty($AutoNotePhasesExist)) {
                            AutoNotePhases::where('id', $note_phase_id)->update($values);
                            $success++;
                        } else {
                            $error++;
                        }
                    }
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }

        $response['success'] = true;
        $response['message'] = 'Setting saved successfully!';
        return json_encode($response);
    }

    /**
     * [POST] Process Auto Note Phase Settings all add phase changes
     */
    public function phaseSettingsAddPhaseChangesAll(Request $request)
    {
        try {

            $tenant_id = $this->cur_tenant_id;

            $auto_note_details = AutoNoteOccurrences::where([
                'tenant_id' => $tenant_id,
            ])->first();

            $auto_note_phases = AutoNotePhases::where([
                'tenant_id' => $tenant_id,
            ])->get();

            $current_project_typeid = "";


            $Tenant = Tenant::find($tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }

            $obj = new FilevineService($apiurl, "");

            // get project types
            $mappings = [];
            $fv_project_type_list = $obj->getProjectTypeList();
            if ($fv_project_type_list != null) {
                $fv_project_type_list = json_decode($fv_project_type_list, true);
                if ($fv_project_type_list['count']) {
                    foreach ($fv_project_type_list['items'] as $key => $item) {
                        $fv_project_type_phases = json_decode($obj->getProjectTypePhaseList($item['projectTypeId']['native']), true);
                        if (isset($fv_project_type_phases['items'])) {
                            $project_type_phases = $fv_project_type_phases['items'];
                            if (!empty($project_type_phases)) {
                                foreach ($project_type_phases as $projectPhase) {

                                    $AutoNotePhases = AutoNotePhases::where('fv_project_type_id', $item['projectTypeId']['native'])->where('fv_phase_id', $projectPhase['phaseId']['native'])->first();
                                    if (empty($AutoNotePhases)) {
                                        AutoNotePhases::create([
                                            'tenant_id' => $tenant_id,
                                            'fv_project_type_id' => $item['projectTypeId']['native'],
                                            'fv_project_type_name' => $item['name'],
                                            'phase_name' => $projectPhase['name'],
                                            'fv_phase_id' => $projectPhase['phaseId']['native'],
                                            'custom_message' => 'Hello [client_firstname], your case with [law_firm_name] has an update! Log into our Client Portal to review: [client_portal_url]',
                                            'is_active' => 1
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $response['success'] = true;
            $response['message'] = 'Setting saved successfully!';
            return json_encode($response);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }


    /**
     * [POST] Process Auto Note Google Review Settings
     */

    public function processAutoNoteGoogleReviewSettings(Request $request)
    {
        try {
            $tenant_id = $this->cur_tenant_id;

            $google_review_link_id = $request->id;
            $current_date = date('Y-m-d H:i:s');

            if (!empty($request->formData)) {

                foreach ($request->formData as $key => $request) {
                    $is_new = $request['is_new'];
                    $review_link = $request['review_link'] ?? '';
                    $handle_type = $request['handle_type'] ?? '';
                    $is_default = $request['is_default'] ?? null;
                    $zip_code = $request['zip_code'] ?? '';
                    if (empty($request['zip_code'])) {
                        $is_default = 1;
                    }
                    if (!empty($request['review_link']) && !empty($request['handle_type'])) {
                        $google_review_link_id = $request['id'] ?? '';
                        $city_id = $request['city_id'] ?? '';

                        $is_exist_google_review_link = AutoNoteGoogleReviewLinks::where('id', $google_review_link_id)->get();

                        if ($is_new == 1 && count($is_exist_google_review_link) <= 0) {

                            $values = array(
                                'tenant_id' => $tenant_id,
                                'review_link' => $review_link,
                                'description' => $handle_type,
                                'is_default' => $is_default,
                                'created_at' => $current_date,
                                'updated_at' => $current_date
                            );

                            $google_review_added = AutoNoteGoogleReviewLinks::create($values);

                            if (isset($google_review_added->id)) {
                                $values = array(
                                    'auto_note_google_review_link_id' => $google_review_added->id,
                                    'zip_code' => $zip_code,
                                    'created_at' => $current_date,
                                    'updated_at' => $current_date
                                );

                                $google_review_city_added = AutoNoteGoogleReviewCities::create($values);
                            }

                            $response['success'] = true;
                            $response['message'] = 'Setting saved successfully!';
                            $response['new_data'] = $google_review_added;
                        } else {
                            $values = array(
                                'review_link' => $review_link,
                                'description' => $handle_type,
                                'updated_at' => $current_date,
                                'is_default' =>  $is_default,
                            );

                            $city_values = array(
                                'zip_code' => $zip_code,
                                'updated_at' => $current_date
                            );

                            if ($google_review_link_id != "") {
                                AutoNoteGoogleReviewLinks::where('id', $google_review_link_id)->update($values);
                            }

                            if ($city_id != "") {
                                AutoNoteGoogleReviewCities::where('id', $city_id)->update($city_values);
                            }
                            $response['success'] = true;
                            $response['message'] = 'Setting saved successfully!';
                        }
                    } else {
                        $google_review_city_id = $request['city_id2'];

                        $is_exist_google_review_city = AutoNoteGoogleReviewCities::where('id', $google_review_city_id)->get();

                        if ($is_new == 1 && count($is_exist_google_review_city) <= 0) {

                            $values = array(
                                'auto_note_google_review_link_id' => $request['google_review_link_id'],
                                'zip_code' => $zip_code,
                                'created_at' => $current_date,
                                'updated_at' => $current_date
                            );

                            $google_review_city_added = AutoNoteGoogleReviewCities::create($values);

                            $response['success'] = true;
                            $response['message'] = 'Setting saved successfully!';
                            $response['new_data'] = $google_review_city_added;
                        } else {

                            $city_values = array(
                                'zip_code' => $zip_code,
                                'updated_at' => $current_date
                            );

                            AutoNoteGoogleReviewCities::where('id', $google_review_city_id)->update($city_values);

                            $response['success'] = true;
                            $response['message'] = 'Setting saved successfully!';
                        }
                    }
                }
            } else if ($request->delete_action) {
                $response = ['success' => false, 'message' => ''];

                if ($request->delete_action == 'delete_action') {
                    AutoNoteGoogleReviewLinks::where('id', $google_review_link_id)->delete();
                    AutoNoteGoogleReviewCities::where('auto_note_google_review_link_id', $google_review_link_id)->delete();
                    $response['success'] = true;
                    $response['message'] = 'Setting saved successfully!';
                }
            }

            return json_encode($response);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [POST] Process Auto Note Google Review Cities
     */

    public function processAutoNoteGoogleReviewCities(Request $request)
    {
        try {
            $tenant_id = $this->cur_tenant_id;

            $google_review_city_id = $request->city_id;
            $current_date = date('Y-m-d H:i:s');

            if (!empty($request->formData)) {

                foreach ($request->formData as $key => $request) {
                    $google_review_city_id = $request['city_id'];
                    $is_new = $request['is_new'];
                    $zip_code = $request['zip_code'];


                    $is_exist_google_review_city = AutoNoteGoogleReviewCities::where('id', $google_review_city_id)->get();

                    if ($is_new == 1 && count($is_exist_google_review_city) <= 0) {

                        $values = array(
                            'auto_note_google_review_link_id' => $request['google_review_link_id'],
                            'zip_code' => $zip_code,
                            'created_at' => $current_date,
                            'updated_at' => $current_date
                        );

                        $google_review_city_added = AutoNoteGoogleReviewCities::create($values);

                        $response['success'] = true;
                        $response['message'] = 'Setting saved successfully!';
                        $response['new_data'] = $google_review_city_added;
                    } else {

                        $city_values = array(
                            'zip_code' => $zip_code,
                            'updated_at' => $current_date
                        );

                        AutoNoteGoogleReviewCities::where('id', $google_review_city_id)->update($city_values);

                        $response['success'] = true;
                        $response['message'] = 'Setting saved successfully!';
                    }
                }
            } else if ($request->delete_action) {
                $response = ['success' => false, 'message' => ''];

                if ($request->delete_action == 'delete_action') {
                    AutoNoteGoogleReviewCities::where('id', $google_review_city_id)->delete();
                    $response['success'] = true;
                    $response['message'] = 'Setting saved successfully!';
                }
            }

            return json_encode($response);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * [GET] Get Message Log
     */
    public function getMessageLog(Request $request)
    {

        $log_start_date = $request->log_start_date;
        $log_end_date = $request->log_end_date;

        $google_review_reply_messages = DB::table('auto_note_google_review_messages')
            ->join('fv_clients', 'auto_note_google_review_messages.client_id', '=', 'fv_clients.id')
            ->select('auto_note_google_review_messages.*', 'fv_clients.fv_client_name')
            ->where(['auto_note_google_review_messages.tenant_id' => $this->cur_tenant_id])
            ->where(['auto_note_google_review_messages.msg_type' => 'in'])
            ->where('auto_note_google_review_messages.created_at', '>=', $log_start_date)
            ->where('auto_note_google_review_messages.created_at', '<=', $log_end_date . ' 23:59:59')
            ->orderBy('auto_note_google_review_messages.id', 'DESC')
            ->get();

        foreach ($google_review_reply_messages as $google_review) {
            $score = (int) filter_var($google_review->message_body, FILTER_SANITIZE_NUMBER_INT);
            $google_review->score = $score ? $score : '';
        }

        return response()->json([
            'data' => $google_review_reply_messages,
            'status' => true,
        ]);
    }
}
