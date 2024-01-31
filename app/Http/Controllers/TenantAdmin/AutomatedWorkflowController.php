<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Models\AutomatedWorkflowAction;
use App\Models\AutomatedWorkflowActionLog;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as Logging;

use App\Models\Tenant;
use App\Models\AutomatedWorkflowFvSubscription;
use App\Models\AutomatedWorkflowTrigger;
use App\Models\AutomatedWorkflowTriggerFilter;
use App\Models\AutomatedWorkflowLog;
use App\Models\AutomatedWorkflowInitialAction;
use App\Models\AutomatedWorkflowTriggerActionMapping;
use App\Models\TenantForm;
use App\Models\ClientFileUploadConfiguration;

use App\Services\FilevineService;
use App\Services\SendGridServices;
use App\Services\VariableService;

class AutomatedWorkflowController extends Controller
{
    public $cur_tenant_id;
    public $fv_service;
    public $fv_api_base_url;

    public function __construct()
    {
        try {
            Controller::setSubDomainName();
            $this->cur_tenant_id = session()->get('tenant_id');
            $Tenant = Tenant::find($this->cur_tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $this->fv_service = new FilevineService($apiurl, "");
            $this->fv_api_base_url = $apiurl;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * [GET] Automated Workflow Page for Admin
     */
    public function index()
    {
        try {

            // Add action for a tenant if not exist
            $tenant_id = $this->cur_tenant_id;
            $actions = AutomatedWorkflowInitialAction::getStaticActionList();
            foreach ($actions as $key => $value) {
                $is_exist = AutomatedWorkflowInitialAction::where('tenant_id', $tenant_id)->where('action_short_code', $value['action_short_code'])->exists();
                if (!$is_exist) {
                    $value['tenant_id'] = $tenant_id;
                    $value['created_at'] = date('Y-m-d H:i:s');
                    AutomatedWorkflowInitialAction::insert($value);
                }
            }

            $data['actions'] = AutomatedWorkflowInitialAction::where('tenant_id', $tenant_id)->where('action_short_code', '!=', '7')->orderBy('order_by', 'ASC')->get();
            /* $data['disable_add_hashtag'] = false;
            if ($this->fv_api_base_url == "https://api.filevine.io") {
                $data['disable_add_hashtag'] = true;
            } */

            // Get configure trigger
            $triggers = AutomatedWorkflowTrigger::leftJoin('automated_workflow_trigger_filters', function ($join) {
                $join->on('automated_workflow_triggers.id', '=', 'automated_workflow_trigger_filters.trigger_id');
            })->where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->select('automated_workflow_triggers.id as trigger_table_id', 'automated_workflow_triggers.*', 'automated_workflow_trigger_filters.*')
                ->orderBy('automated_workflow_triggers.id', 'DESC')->get();

            foreach ($triggers as $trigger) {
                $trigger->filter = $trigger->is_filter ? $this->prepareFilterItem($trigger) : '';
                $trigger->is_used = AutomatedWorkflowTriggerActionMapping::where('trigger_id', $trigger->trigger_table_id)->exists();
                $trigger->primary_trigger_display = $trigger->primary_trigger == 'Note' ? 'Task' : $trigger->primary_trigger;
            }
            $data['triggers'] = $triggers;

            // Get map workflow data
            $initial_actions = AutomatedWorkflowInitialAction::where('tenant_id', $tenant_id)->where('is_active', true)->where('action_short_code', '!=', '7')->orderBy('order_by', 'ASC')->get();
            /* if ($this->fv_api_base_url == "https://api.filevine.io") {
                foreach ($initial_actions as $ini_action) {
                    $ini_action->disabled = ($ini_action->action_short_code == '6' ? 'disabled' : '');
                }
            } */
            $data['initial_actions'] = $initial_actions;

            $data['initial_triggers'] = AutomatedWorkflowTrigger::where('tenant_id', $tenant_id)->where('is_active', true)->get();

            $action_maps = AutomatedWorkflowAction::join('automated_workflow_initial_actions', 'automated_workflow_initial_actions.id', '=', 'automated_workflow_actions.automated_workflow_initial_action_id')
                ->join('automated_workflow_trigger_action_mappings', 'automated_workflow_actions.id', '=', 'automated_workflow_trigger_action_mappings.action_id')
                ->join('automated_workflow_triggers', 'automated_workflow_triggers.id', '=', 'automated_workflow_trigger_action_mappings.trigger_id')
                ->where('automated_workflow_actions.tenant_id', $tenant_id)
                ->where('automated_workflow_initial_actions.tenant_id', $tenant_id)
                ->where('automated_workflow_trigger_action_mappings.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->whereNull('automated_workflow_trigger_action_mappings.deleted_at')
                ->select('automated_workflow_actions.*', 'automated_workflow_initial_actions.action_short_code', 'automated_workflow_initial_actions.action_name as ini_action_name', 'automated_workflow_triggers.trigger_name', 'automated_workflow_triggers.id as trigger_id', 'automated_workflow_trigger_action_mappings.id as map_id', 'automated_workflow_trigger_action_mappings.workflow_description', 'automated_workflow_trigger_action_mappings.status as status')
                ->orderBy('automated_workflow_trigger_action_mappings.id', 'DESC')->get();
            foreach ($action_maps as $action_map) {
                $action_map->action_body =  $this->prepareActionBody($action_map);
            }
            $data['action_maps'] = $action_maps;

            $configure_actions = AutomatedWorkflowAction::join('automated_workflow_initial_actions', 'automated_workflow_initial_actions.id', '=', 'automated_workflow_actions.automated_workflow_initial_action_id')
                ->where('automated_workflow_actions.tenant_id', $tenant_id)
                ->where('automated_workflow_initial_actions.tenant_id', $tenant_id)
                ->select('automated_workflow_actions.*', 'automated_workflow_initial_actions.action_short_code', 'automated_workflow_initial_actions.action_name as ini_action_name')
                ->orderBy('automated_workflow_actions.id', 'DESC')->get();
            foreach ($configure_actions as $configure_action) {
                $configure_action->action_body =  $this->prepareActionBody($configure_action);
                $configure_action->is_used = AutomatedWorkflowTriggerActionMapping::where('action_id', $configure_action->id)->exists();
            }
            $data['configure_actions'] = $configure_actions;

            $data['eligible_actions'] = AutomatedWorkflowAction::where('tenant_id', $tenant_id)->where('is_active', true)->select('id', 'action_name')->orderBy('id', 'DESC')->get();

            $trigger_id_query = DB::table('automated_workflow_triggers')->where('tenant_id', $tenant_id)
                ->where('is_active', true);
            $trigger_ids = $trigger_id_query->join('automated_workflow_trigger_action_mapping_rules', function ($join) {
                $join->on('automated_workflow_trigger_action_mapping_rules.primary_trigger', '=', 'automated_workflow_triggers.primary_trigger');
                $join->on('automated_workflow_trigger_action_mapping_rules.trigger_event', '=', 'automated_workflow_triggers.trigger_event');
            })->select('automated_workflow_triggers.id', DB::raw('group_concat(automated_workflow_trigger_action_mapping_rules.action_short_code) as action_short_code'))
                ->groupBy('automated_workflow_triggers.id')
                ->get();

            $trigger_id_query_no_event = DB::table('automated_workflow_triggers')->where('tenant_id', $tenant_id)
                ->where('is_active', true)->whereNull('automated_workflow_triggers.trigger_event');
            $trigger_ids_no_event = $trigger_id_query_no_event->join('automated_workflow_trigger_action_mapping_rules', function ($join) {
                $join->on('automated_workflow_trigger_action_mapping_rules.primary_trigger', '=', 'automated_workflow_triggers.primary_trigger');
            })->select('automated_workflow_triggers.id', DB::raw('group_concat(automated_workflow_trigger_action_mapping_rules.action_short_code) as action_short_code'))
                ->groupBy('automated_workflow_triggers.id')
                ->get();

            $trigger_ids = $trigger_ids->merge($trigger_ids_no_event);
            $trigger_ids = $trigger_ids->all();

            $trigger_action_rules = [];
            foreach ($trigger_ids as $record) {
                $action_short_codes = explode(",", $record->action_short_code);
                $action_ids = DB::table('automated_workflow_actions')->join('automated_workflow_initial_actions', 'automated_workflow_initial_actions.id', '=', 'automated_workflow_actions.automated_workflow_initial_action_id')
                    ->where('automated_workflow_actions.tenant_id', $tenant_id)
                    ->where('automated_workflow_initial_actions.tenant_id', $tenant_id)
                    ->whereIn('automated_workflow_initial_actions.action_short_code', $action_short_codes)
                    ->where('automated_workflow_actions.is_active', true)->pluck('automated_workflow_actions.id')->toArray();
                $trigger_action_rules[$record->id] = $action_ids;
            }
            $data['trigger_action_rules'] = $trigger_action_rules;

            $data['tenant_forms'] = TenantForm::where('tenant_id', $tenant_id)->where('is_active', true)->get();
            $data['client_file_upload_configurations'] = ClientFileUploadConfiguration::where('tenant_id', $tenant_id)->get();


            return $this->_loadContent("admin.pages.automated_workflow", $data);
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
     *  [GET] Get List of Project Type
     */
    public function getProjectTypeList()
    {
        try {
            $fv_project_type_list = json_decode($this->fv_service->getProjectTypeList());
            $options = '<option value="">Select Project Type</option>';
            if (isset($fv_project_type_list->items) and !empty($fv_project_type_list->items)) {
                $project_type_lists = $fv_project_type_list->items;
                foreach ($project_type_lists as $project_type) {
                    $options .= '<option value="' . $project_type->projectTypeId->native . '">' . $project_type->name  . '</option>';
                }
            }
            return response()->json([
                'status'  => true,
                'html' => $options
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     *  [GET] Get Project Phase List by Project Type ID
     */
    public function getProjectTypePhaseList(Request $request)
    {
        try {
            $project_type_id = $request->project_type_id;
            $phase_lists = json_decode($this->fv_service->getProjectTypePhaseList($project_type_id));
            $options = '<option value="">Select Phase</option>';
            if (isset($phase_lists->items) and !empty($phase_lists->items)) {
                $phase_lists = $phase_lists->items;
                foreach ($phase_lists as $phase_list) {
                    $options .= '<option value="' . $phase_list->phaseId->native . '">' . $phase_list->name  . '</option>';
                }
            }
            return response()->json([
                'status'  => true,
                'html' => $options
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     *  [GET] Get Contact Meta Data
     */
    public function getContactMetadata()
    {
        try {

            $accepted_types = ['adjuster', 'employee', 'hospital', 'staff', 'user', 'attorney', 'client', 'court', 'defendant', 'expert', 'insurance company', 'involved party', 'judge', 'investigating agency', 'medical examiner', 'treatment provider'];

            $contact_metas = json_decode($this->fv_service->getContactMetadata());
            $options = '<option value="">Person Types</option>';
            if (isset($contact_metas) && !empty($contact_metas)) {
                foreach ($contact_metas as $contact_meta) {
                    if ($contact_meta->selector == 'personTypes') {
                        if (isset($contact_meta->allowedValues)) {
                            $allowedValues = $contact_meta->allowedValues;
                            foreach ($allowedValues as $allowedValue) {
                                if (in_array(strtolower($allowedValue->name), $accepted_types)) {
                                    $options .= '<option value="' . $allowedValue->value . '">' . $allowedValue->name  . '</option>';
                                }
                            }
                        }
                    }
                }
            }
            return response()->json([
                'status'  => true,
                'html' => $options
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     *  [GET] Get User List of FV
     */
    public function getUserList()
    {
        try {
            $user_lists = json_decode($this->fv_service->getAllUsersList());
            $options = '<option value="">Select User</option>';
            if (isset($user_lists->items) and !empty($user_lists->items)) {
                foreach ($user_lists->items as $user_list) {
                    $options .= '<option value="' . $user_list->user->userId->native . '">' . $user_list->user->firstName . ' ' . (isset($user_list->user->lastName) ? $user_list->user->lastName : '')    . '</option>';
                }
            }
            return response()->json([
                'status'  => true,
                'html' => $options
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     *  [POST] Get Section List By Project Type ID
     */
    public function getSectionListByProjectType(Request $request)
    {
        try {
            $project_type_id = $request->project_type_id;
            $is_collection = $request->is_collection;
            $fv_project_type_section_list = json_decode($this->fv_service->getProjectTypeSectionList($project_type_id), true);
            $options = '<option value="">Choose Section</option>';
            if (isset($fv_project_type_section_list['items']) and !empty($fv_project_type_section_list['items'])) {
                $sections = collect($fv_project_type_section_list['items']);
                if ($is_collection == 'static') {
                    $sections = $sections->where("isCollection", false);
                }
                foreach ($sections as $key => $section) {
                    $options .= '<option value="' . $section['sectionSelector'] . '">' . $section['name'] . '</option>';
                }
            }
            return response()->json([
                'status'  => true,
                'html' => $options
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     *  [GET] Get Collection List By Project Type ID
     */
    public function getCollectionListByProjectType(Request $request)
    {
        try {
            $project_type_id = $request->project_type_id;
            $fv_project_type_section_list = json_decode($this->fv_service->getProjectTypeSectionList($project_type_id), true);
            $options = '<option value="">Choose Section</option>';
            if (isset($fv_project_type_section_list['items']) and !empty($fv_project_type_section_list['items'])) {
                $sections = collect($fv_project_type_section_list['items']);
                $sectionsWithIsCollection = $sections->where("isCollection", true);
                foreach ($sectionsWithIsCollection as $key => $section) {
                    $options .= '<option value="' . $section['sectionSelector'] . '">' . $section['name'] . ' (' . $section['sectionSelector'] . ')' . '</option>';
                }
            }
            return response()->json([
                'status'  => true,
                'html' => $options
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     *  [GET] Get Project Section By Project Type ID
     */
    public function getProjectSectionField(Request $request)
    {
        try {
            $project_type_id = $request->project_type_id;
            $project_section_selector = $request->project_section_selector;
            $custom_field_types = $request->custom_field_types;
            $fv_project_type_section_field_list = json_decode($this->fv_service->getProjectTypeSectionFieldList($project_type_id, $project_section_selector), true);
            $options = '<option value="">--Select--</option>';
            if (isset($fv_project_type_section_field_list['customFields']) and !empty($fv_project_type_section_field_list['customFields'])) {
                $customFields = collect($fv_project_type_section_field_list['customFields']);
                if ($custom_field_types == 'PersonLink') {
                    $customFields = $customFields->where("customFieldType", "PersonLink");
                } else if ($custom_field_types == 'mirror') {
                    $customFields = $customFields->whereIn("customFieldType", ['PersonLink', 'PersonList', 'String', 'StringList', 'Integer', 'Text', 'TextLarge', 'Date', 'Dropdown', 'Boolean', 'Currency', 'Percent']);
                } else if ($custom_field_types != 'all') {
                    $customFields = $customFields->where("customFieldType", "ActionButton");
                }
                foreach ($customFields as $key => $field) {
                    $options .= '<option value="' . $field['fieldSelector'] . '">' . $field['name'] . '(' . $field['customFieldType'] . ')' . '</option>';
                }
            }
            return response()->json([
                'status'  => true,
                'html' => $options
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     *  [POST] Save Trigger Data
     */
    public function save(Request $request)
    {
        try {

            $edit_trigger_id   = $request->input('trigger_id');
            $trigger_name   = $request->input('trigger_name');
            $tenant_id = $this->cur_tenant_id;

            if (empty($edit_trigger_id)) {
                $validator = Validator::make($request->all(), [
                    'trigger_name'       => 'required',
                    'primary_trigger'       => 'required',
                ]);
                if ($validator->fails()) {
                    $message = "Validation Failed! ";
                    foreach ($validator->errors()->all() as $key => $value) {
                        $message .= $value . " ";
                    }
                    return response()->json([
                        'status'  => false,
                        'message' => $message
                    ]);
                }
                $name_exist = AutomatedWorkflowTrigger::where('tenant_id', $tenant_id)->where('trigger_name', $trigger_name)->exists();
            } else {
                $name_exist = AutomatedWorkflowTrigger::where('tenant_id', $tenant_id)->where('id', '!=', $edit_trigger_id)->where('trigger_name', $trigger_name)->exists();
            }

            if ($name_exist) {
                return response()->json([
                    'status'  => false,
                    'message' => "Trigger name has already been taken. Enter a different name."
                ]);
            }

            $primary_trigger    = $request->input('primary_trigger');
            $trigger_event  = $request->input('trigger_event');
            $filter_selection   = $request->input('filter_selection');
            $project_type_id    = $request->input('project_type_id');
            $project_type_name  = $request->input('project_type_name');
            $phase_name_id  = $request->input('phase_name_id');
            $phase_name  = $request->input('phase_name');
            $filter_contact_by  = $request->input('filter_contact_by');
            $person_type_selection_id   = $request->input('person_type_selection_id');
            $person_type_selection_name = $request->input('person_type_selection_name');
            $filter_task_by = $request->input('filter_task_by');
            $org_user_id    = $request->input('org_user_id');
            $org_user_name  = $request->input('org_user_name');
            $project_section_selector   = $request->input('project_section_selector');
            $project_section_selector_name  = $request->input('project_section_selector_name');
            $project_section_field_selector = $request->input('project_section_field_selector');
            $project_section_field_name = $request->input('project_section_field_name');
            $filter_appointment_by  = $request->input('filter_appointment_by');
            $project_hashtag    = $request->input('project_hashtag');

            $tenant_form_id    = $request->input('tenant_form_id');
            $tenant_form_name    = $request->input('tenant_form_name');
            $client_file_upload_configuration_id    = $request->input('client_file_upload_configuration_id');
            $client_file_upload_configuration_name    = $request->input('client_file_upload_configuration_name');
            $sms_line    = $request->input('sms_line');

            $tenant_id = $this->cur_tenant_id;

            if ($edit_trigger_id) {
                $automatedWorkflowTrigger = AutomatedWorkflowTrigger::find($edit_trigger_id);
            } else {
                $automatedWorkflowTrigger = new AutomatedWorkflowTrigger;
                $automatedWorkflowTrigger->tenant_id = $tenant_id;
                $automatedWorkflowTrigger->primary_trigger = $primary_trigger;
                $automatedWorkflowTrigger->trigger_event = $trigger_event;
            }
            $automatedWorkflowTrigger->trigger_name  = $trigger_name;
            $automatedWorkflowTrigger->is_filter  = $filter_selection;
            $automatedWorkflowTrigger->save();
            $trigger_id = $automatedWorkflowTrigger->id;

            // Create Subscription
            if ($primary_trigger == 'Note' && $trigger_event == 'TaskflowButtonTrigger') {
                $fv_subscription = $this->createWebhookSubscriptionInFilevine('Taskflow', 'Executed', $trigger_name . ' Taskflow - Executed');
                //$fv_subscription = $this->createWebhookSubscriptionInFilevine('Taskflow', 'Reset', $trigger_name . ' Taskflow - Reset');
            } else {
                $fv_subscription = $this->createWebhookSubscriptionInFilevine($primary_trigger, $trigger_event, $trigger_name);
            }

            if ($filter_selection) {
                if ($edit_trigger_id) {
                    $automatedWorkflowTriggerFilter = AutomatedWorkflowTriggerFilter::where('trigger_id', $trigger_id)->where('tenant_id', $tenant_id)->first();
                    $automatedWorkflowTriggerFilter->fv_task_filter_type_name = null;
                    $automatedWorkflowTriggerFilter->fv_task_hashtag = null;
                    $automatedWorkflowTriggerFilter->fv_task_assigned_user_id = 0;
                    $automatedWorkflowTriggerFilter->fv_task_assigned_user_name = null;
                    $automatedWorkflowTriggerFilter->fv_task_created_user_id = 0;
                    $automatedWorkflowTriggerFilter->fv_task_created_user_name = null;
                    $automatedWorkflowTriggerFilter->fv_task_completed_user_id = 0;
                    $automatedWorkflowTriggerFilter->fv_task_completed_user_name = null;
                    $automatedWorkflowTriggerFilter->fv_taskflow_project_type_id = 0;
                    $automatedWorkflowTriggerFilter->fv_taskflow_project_type_name = null;
                    $automatedWorkflowTriggerFilter->fv_taskflow_section_id = null;
                    $automatedWorkflowTriggerFilter->fv_taskflow_section_name = null;
                    $automatedWorkflowTriggerFilter->fv_taskflow_field_id = null;
                    $automatedWorkflowTriggerFilter->fv_taskflow_field_name = null;
                    $automatedWorkflowTriggerFilter->fv_calendar_hashtag = null;
                    $automatedWorkflowTriggerFilter->fv_calendar_attendee_user_id = 0;
                    $automatedWorkflowTriggerFilter->fv_calendar_attendee_user_name = null;
                } else {
                    $automatedWorkflowTriggerFilter = new AutomatedWorkflowTriggerFilter;
                }

                $automatedWorkflowTriggerFilter->tenant_id = $tenant_id;
                $automatedWorkflowTriggerFilter->trigger_id = $trigger_id;
                if ($primary_trigger == 'Project' && $trigger_event == 'Created') {
                    $automatedWorkflowTriggerFilter->fv_project_type_id = $project_type_id;
                    $automatedWorkflowTriggerFilter->fv_project_type_name = $project_type_name;
                } else if ($primary_trigger == 'Project' && $trigger_event == 'PhaseChanged') {
                    $automatedWorkflowTriggerFilter->fv_project_type_id = $project_type_id;
                    $automatedWorkflowTriggerFilter->fv_project_type_name = $project_type_name;
                    $automatedWorkflowTriggerFilter->fv_project_phase_id = $phase_name_id;
                    $automatedWorkflowTriggerFilter->fv_project_phase_name = $phase_name;
                } else if ($primary_trigger == 'Project' && $trigger_event == 'AddedHashtag') {
                    $automatedWorkflowTriggerFilter->fv_project_hashtag = $project_hashtag;
                } else if ($primary_trigger == 'Contact') {
                    $automatedWorkflowTriggerFilter->fv_contact_filter_type_name = $filter_contact_by;
                    $automatedWorkflowTriggerFilter->fv_contact_person_type_id = $person_type_selection_id;
                    $automatedWorkflowTriggerFilter->fv_contact_person_type_name = $person_type_selection_name;
                } else if ($primary_trigger == 'Note') {
                    if ($filter_task_by == 'Task Hashtags' || $filter_task_by == 'Auto-Generated Task') {
                        $automatedWorkflowTriggerFilter->fv_task_filter_type_name = $filter_task_by;
                        $automatedWorkflowTriggerFilter->fv_task_hashtag = $project_hashtag;
                    } else if ($filter_task_by == 'Assigned To') {
                        $automatedWorkflowTriggerFilter->fv_task_filter_type_name = $filter_task_by;
                        $automatedWorkflowTriggerFilter->fv_task_assigned_user_id = $org_user_id;
                        $automatedWorkflowTriggerFilter->fv_task_assigned_user_name = $org_user_name;
                    } else if ($filter_task_by == 'Created By') {
                        $automatedWorkflowTriggerFilter->fv_task_filter_type_name = $filter_task_by;
                        $automatedWorkflowTriggerFilter->fv_task_created_user_id = $org_user_id;
                        $automatedWorkflowTriggerFilter->fv_task_created_user_name = $org_user_name;
                    } else if ($filter_task_by == 'Completed By') {
                        $automatedWorkflowTriggerFilter->fv_task_filter_type_name = $filter_task_by;
                        $automatedWorkflowTriggerFilter->fv_task_completed_user_id = $org_user_id;
                        $automatedWorkflowTriggerFilter->fv_task_completed_user_name = $org_user_name;
                    } else if ($trigger_event == 'TaskflowButtonTrigger') {
                        $automatedWorkflowTriggerFilter->fv_taskflow_project_type_id = $project_type_id;
                        $automatedWorkflowTriggerFilter->fv_taskflow_project_type_name = $project_type_name;
                        $automatedWorkflowTriggerFilter->fv_taskflow_section_id = $project_section_selector;
                        $automatedWorkflowTriggerFilter->fv_taskflow_section_name = $project_section_selector_name;
                        $automatedWorkflowTriggerFilter->fv_taskflow_field_id = $project_section_field_selector;
                        $automatedWorkflowTriggerFilter->fv_taskflow_field_name = $project_section_field_name;
                    }
                } else if ($primary_trigger == 'CollectionItem') {
                    $automatedWorkflowTriggerFilter->fv_collection_item_project_type_id = $project_type_id;
                    $automatedWorkflowTriggerFilter->fv_collection_item_project_type_name = $project_type_name;
                    $automatedWorkflowTriggerFilter->fv_collection_item_section_id = $project_section_selector;
                    $automatedWorkflowTriggerFilter->fv_collection_item_section_name = $project_section_selector_name;
                    $automatedWorkflowTriggerFilter->fv_collection_item_field_id = $project_section_field_selector;
                    $automatedWorkflowTriggerFilter->fv_collection_item_field_name = $project_section_field_name;
                } else if ($primary_trigger == 'Appointment') {
                    $automatedWorkflowTriggerFilter->fv_calendar_filter_type_name = $filter_appointment_by;
                    if ($filter_appointment_by == 'Note Hashtag') {
                        $automatedWorkflowTriggerFilter->fv_calendar_hashtag = $project_hashtag;
                    } else if ($filter_appointment_by == 'Attendee') {
                        $automatedWorkflowTriggerFilter->fv_calendar_attendee_user_id = $org_user_id;
                        $automatedWorkflowTriggerFilter->fv_calendar_attendee_user_name = $org_user_name;
                    }
                } else if ($primary_trigger == 'Section') {
                    $automatedWorkflowTriggerFilter->fv_section_toggled_project_type_id = $project_type_id;
                    $automatedWorkflowTriggerFilter->fv_section_toggled_project_type_name = $project_type_name;
                    $automatedWorkflowTriggerFilter->fv_section_toggled_section_id = $project_section_selector;
                    $automatedWorkflowTriggerFilter->fv_section_toggled_section_name = $project_section_selector_name;
                } else if ($primary_trigger == 'DocumentUploaded') {
                    $automatedWorkflowTriggerFilter->client_file_upload_configuration_id = $client_file_upload_configuration_id;
                    $automatedWorkflowTriggerFilter->client_file_upload_configuration_name = $client_file_upload_configuration_name;
                } else if ($primary_trigger == 'FormSubmitted') {
                    $automatedWorkflowTriggerFilter->tenant_form_id = $tenant_form_id;
                    $automatedWorkflowTriggerFilter->tenant_form_name = $tenant_form_name;
                } else if ($primary_trigger == 'SMSReceived') {
                    $automatedWorkflowTriggerFilter->sms_line = $sms_line;
                }
                $automatedWorkflowTriggerFilter->save();
            }

            return response()->json([
                'status'  => true,
                'fv_subscription' => $fv_subscription,
                'message' => "Setting saved successfully!"
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     *  Update Trigger Active
     */
    public function updateTriggerActive(Request $request)
    {
        try {
            $trigger_active = $request->input('trigger_active');
            if ($trigger_active == 'all') {
                AutomatedWorkflowTrigger::where('tenant_id', $this->cur_tenant_id)->update(['is_active' => true]);
            } else {
                $trigger_id = $request->input('trigger_id');
                $trigger = AutomatedWorkflowTrigger::where('id', '=', $trigger_id)->where('tenant_id', '=', $this->cur_tenant_id)->first();
                if ($trigger) {
                    $trigger->is_active = $trigger->is_active ? false : true;
                    $trigger->save();
                }
            }
            return response()->json([
                'status'  => true,
                'message' => "Setting saved successfully!"
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     *  Create Filevine Subscription
     */
    public function createWebhookSubscriptionInFilevine($trigger, $event, $name)
    {
        try {

            $tenant_id = $this->cur_tenant_id;
            if ($trigger == 'Section' && ($event == 'Visible' || $event == 'Hidden')) {
                $event = 'Toggle';
            } else if ($trigger == 'ProjectRelation') {
                $trigger = 'Project';
            } else if ($trigger == 'Project' && $event == 'AddedHashtag') {
                $event = 'Updated';
            }
            $eventId = $trigger . "." . $event;

            $webhook_urls = [
                'Project.Created' => url('api/v1/webhook/automated_workflow_project_created'),
                'Project.Updated' => url('api/v1/webhook/automated_workflow_project_updated'),
                'Project.PhaseChanged' => url('api/v1/webhook/automated_workflow_project_phase_changed'),
                'Project.Related' => url('api/v1/webhook/automated_workflow_project_related'),
                'Project.Unrelated' => url('api/v1/webhook/automated_workflow_project_unrelated'),
                'Contact.Created' => url('api/v1/webhook/automated_workflow_contact_created'),
                'Contact.Updated' => url('api/v1/webhook/automated_workflow_contact_updated'),
                'Note.Created' => url('api/v1/webhook/automated_workflow_note_created'),
                'Note.Completed' => url('api/v1/webhook/automated_workflow_note_completed'),
                'CollectionItem.Created' => url('api/v1/webhook/automated_workflow_collection_item_created'),
                'CollectionItem.Deleted' => url('api/v1/webhook/automated_workflow_collection_item_deleted'),
                'Appointment.Created' => url('api/v1/webhook/automated_workflow_appointment_created'),
                'Appointment.Updated' => url('api/v1/webhook/automated_workflow_appointment_updated'),
                'Appointment.Deleted' => url('api/v1/webhook/automated_workflow_appointment_deleted'),
                'Section.Toggle' => url('api/v1/webhook/automated_workflow_section_toggle'),
                'Taskflow.Executed' => url('api/v1/webhook/automated_workflow_taskflow_executed'),
                'Taskflow.Reset' => url('api/v1/webhook/automated_workflow_taskflow_reset'),
            ];

            if (array_key_exists($eventId, $webhook_urls)) {
                $webhook_url = $webhook_urls[$eventId];
            } else {
                return false;
            }

            $filevine_exist = false;
            $filevine_creation = false;
            $subscriptionId = null;
            $get_all_subscriptions = json_decode($this->fv_service->getSubscriptionsList());
            foreach ($get_all_subscriptions as $single_subscription) {
                if (isset($single_subscription->eventIds) && $single_subscription->eventIds == [$eventId] && isset($single_subscription->endpoint) && $single_subscription->endpoint == $webhook_url) {
                    $filevine_exist = true;
                    $subscriptionId = $single_subscription->subscriptionId;
                }
            }

            if (!$filevine_exist) {
                $filevine_obj = json_decode($this->fv_service->createSubscription($name, $webhook_url, $eventId));
                $subscriptionId = isset($filevine_obj->subscriptionId) ? $filevine_obj->subscriptionId : '';
                $filevine_creation = !empty($subscriptionId) ? true : false;
            }

            $is_exist_db = AutomatedWorkflowFvSubscription::where('tenant_id', $tenant_id)
                ->where('fv_subscription_event', $eventId)->count();
            if (!$is_exist_db && $filevine_creation) {
                $automatedWorkflowFvSubscription = new AutomatedWorkflowFvSubscription;
                $automatedWorkflowFvSubscription->tenant_id = $tenant_id;
                $automatedWorkflowFvSubscription->fv_subscription_id = $subscriptionId;
                $automatedWorkflowFvSubscription->fv_subscription_link = $webhook_url;
                $automatedWorkflowFvSubscription->fv_subscription_event = $eventId;
                $automatedWorkflowFvSubscription->save();
            }

            $response = array('filevine_creation' => $filevine_creation, 'filevine_exist' => $filevine_exist);
            return $response;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }


    /**
     *  [POST] Delete a Trigger
     */
    public function delete(Request $request)
    {
        try {
            $trigger_id = $request->input('trigger_id');
            AutomatedWorkflowTrigger::where('id', $trigger_id)->where('tenant_id', $this->cur_tenant_id)->delete();
            AutomatedWorkflowTriggerFilter::where('trigger_id', $trigger_id)->where('tenant_id', $this->cur_tenant_id)->delete();

            return response()->json([
                'status'  => true,
                'message' => "Setting saved successfully!"
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     *  Custom Function to Prepare Filter of String
     */
    public function prepareFilterItem($trigger)
    {
        $filter_str = "";
        if ($trigger->fv_project_type_name) {
            $filter_str .= " Project Type=" . $trigger->fv_project_type_name . ";";
        }
        if ($trigger->fv_project_phase_name) {
            $filter_str .= " Phase Name=" . $trigger->fv_project_phase_name . ";";
        }
        if ($trigger->fv_project_hashtag) {
            $filter_str .= " Hashtag=" . $trigger->fv_project_hashtag . ";";
        }
        if ($trigger->fv_contact_filter_type_name) {
            $filter_str .= " Filter Type=" . $trigger->fv_contact_filter_type_name . ";";
        }
        if ($trigger->fv_contact_person_type_name) {
            $filter_str .= " Person Type=" . $trigger->fv_contact_person_type_name . ";";
        }
        if ($trigger->fv_contact_hashtag) {
            $filter_str .= " Hashtag=" . $trigger->fv_contact_hashtag . ";";
        }
        if ($trigger->fv_task_filter_type_name) {
            $filter_str .= " Filter Type=" . $trigger->fv_task_filter_type_name . ";";
        }
        if ($trigger->fv_task_hashtag) {
            $filter_str .= " Hashtag=" . $trigger->fv_task_hashtag . ";";
        }
        if ($trigger->fv_task_assigned_user_name) {
            $filter_str .= " Assigned User=" . $trigger->fv_task_assigned_user_name . ";";
        }
        if ($trigger->fv_task_created_user_name) {
            $filter_str .= " Created User=" . $trigger->fv_task_created_user_name . ";";
        }
        if ($trigger->fv_task_completed_user_name) {
            $filter_str .= " Completed By=" . $trigger->fv_task_completed_user_name . ";";
        }
        if ($trigger->fv_taskflow_project_type_name) {
            $filter_str .= " Project Type=" . $trigger->fv_taskflow_project_type_name . ";";
        }
        if ($trigger->fv_taskflow_section_name) {
            $filter_str .= " Section=" . $trigger->fv_taskflow_section_name . ";";
        }
        if ($trigger->fv_taskflow_field_name) {
            $filter_str .= " Field=" . $trigger->fv_taskflow_field_name . ";";
        }
        if ($trigger->fv_collection_item_project_type_name) {
            $filter_str .= " Project Type=" . $trigger->fv_collection_item_project_type_name . ";";
        }
        if ($trigger->fv_collection_item_section_name) {
            $filter_str .= " Section=" . $trigger->fv_collection_item_section_name . ";";
        }
        if ($trigger->fv_collection_item_field_name) {
            $filter_str .= " Field=" . $trigger->fv_collection_item_field_name . ";";
        }
        if ($trigger->fv_calendar_filter_type_name) {
            $filter_str .= " Filter Type=" . $trigger->fv_calendar_filter_type_name . ";";
        }
        if ($trigger->fv_calendar_hashtag) {
            $filter_str .= " Hashtag=" . $trigger->fv_calendar_hashtag . ";";
        }
        if ($trigger->fv_calendar_attendee_user_name) {
            $filter_str .= " Attendee User=" . $trigger->fv_calendar_attendee_user_name . ";";
        }
        if ($trigger->fv_section_toggled_project_type_name) {
            $filter_str .= " Project Type=" . $trigger->fv_section_toggled_project_type_name . ";";
        }
        if ($trigger->fv_section_toggled_section_name) {
            $filter_str .= " Section=" . $trigger->fv_section_toggled_section_name . ";";
        }
        if ($trigger->tenant_form_name) {
            $filter_str .= " Form Name=" . $trigger->tenant_form_name . ";";
        }
        if ($trigger->client_file_upload_configuration_name) {
            $filter_str .= " File Upload Scheme=" . $trigger->client_file_upload_configuration_name . ";";
        }
        if ($trigger->sms_line) {
            $filter_str .= " SMS Line=" . $trigger->sms_line . ";";
        }
        return rtrim($filter_str, ';');
    }

    /**
     *  [GET] Automated workflow action logs
     */
    public function webhookLog(Request $request)
    {
        try {
            $log_start_date = $request->log_start_date;
            $log_end_date = $request->log_end_date;
            $tenant_id = $this->cur_tenant_id;

            // $logs = DB::table('automated_workflow_logs')->join('automated_workflow_action_logs', 'automated_workflow_logs.id', '=', 'automated_workflow_action_logs.automated_workflow_log_id')
            //     ->where('automated_workflow_logs.tenant_id', $tenant_id)
            //     ->where('automated_workflow_action_logs.tenant_id', $tenant_id)
            //     ->where('automated_workflow_logs.created_at', '>=', $log_start_date)
            //     ->where('automated_workflow_logs.created_at', '<=', $log_end_date . ' 23:59:59')
            //     ->select('automated_workflow_logs.*', 'automated_workflow_logs.id as log_id','automated_workflow_action_logs.*')
            //     ->get();

            $logs = DB::table('automated_workflow_logs')->where('automated_workflow_logs.tenant_id', $tenant_id)
                ->where('automated_workflow_logs.created_at', '>=', $log_start_date)
                ->where('automated_workflow_logs.created_at', '<=', $log_end_date . ' 23:59:59')
                ->select('automated_workflow_logs.*', 'automated_workflow_logs.id as log_id')
                ->get();

            foreach ($logs as $log) {
                $request_json = json_decode($log->webhook_request_json);
                $log->ProjectId = (isset($request_json->ProjectId) && !empty($request_json->ProjectId)) ? $request_json->ProjectId : '';
                $log->log_details = json_encode(AutomatedWorkflowActionLog::where('automated_workflow_log_id', $log->log_id)->get());
                $mapinfo = AutomatedWorkflowTriggerActionMapping::select(DB::raw('group_concat(id) as map_ids'), DB::raw('group_concat(action_id) as action_ids'))->where('trigger_id', $log->trigger_id)->first();
                $log->map_ids = isset($mapinfo->map_ids) ? $mapinfo->map_ids : '';
                $log->action_ids = isset($mapinfo->action_ids) ? $mapinfo->action_ids : '';
                $log->map_workflow_description = isset($mapinfo->workflow_description) ? $mapinfo->workflow_description : '';
            }

            return response()->json([
                'data' => $logs,
                'status' => true,
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     *  [POST] Automated workflow action trigger update status
     */
    public function updateStatus(Request $request)
    {
        try {
            $trigger_id = $request->input('trigger_id');
            $trigger = AutomatedWorkflowTrigger::where('id', '=', $trigger_id)->where('tenant_id', '=', $this->cur_tenant_id)->first();
            if ($trigger) {
                if ($request->input('is_test_click')) {
                    $trigger->is_live = $trigger->is_test;
                    $trigger->is_test = $trigger->is_test ? 0 : 1;
                }

                if ($request->input('is_live_click')) {
                    $trigger->is_test = $trigger->is_live;
                    $trigger->is_live = $trigger->is_live ? 0 : 1;
                }

                $trigger->save();
            }

            return response()->json([
                'status'  => true,
                'message' => "Setting saved successfully!"
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     *  [POST] Automated workflow action update status
     */
    public function updateActionStatus(Request $request)
    {
        try {
            $action_change = $request->input('action_change');
            $tenant_id = $this->cur_tenant_id;
            if ($action_change == 'all') {
                AutomatedWorkflowAction::where('tenant_id', $tenant_id)->update(['is_active' => 1]);
            } else {
                $action_id = $request->input('action_id');
                $action = AutomatedWorkflowAction::where('id', $action_id)->where('tenant_id', $this->cur_tenant_id)->first();
                if ($action) {
                    if ($action->is_active) {
                        $action->is_active = false;
                    } else {
                        $action->is_active = true;
                    }
                    $action->save();
                }
            }
            return response()->json([
                'status'  => true,
                'message' => "Setting saved successfully!"
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     *  [POST] Automated workflow action update data
     */
    public function updateActionData(Request $request)
    {
        try {
            $action_id = $request->input('action_id');
            $action_description = $request->input('action_description');
            $is_active = $request->input('is_active') == 'on' ? 1 : 0;
            $action = AutomatedWorkflowInitialAction::where('id', $action_id)->where('tenant_id', $this->cur_tenant_id)->first();
            if ($action) {
                $action->action_description = $action_description;
                $action->is_active = $is_active;
                $action->save();
            }
            return redirect(url("/admin/automated_workflow"))->with('success', "Setting saved successfully!");
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }


    /**
     *  Add Action
     */
    public function addAction(Request $request)
    {
        try {
            $tenant_id = $this->cur_tenant_id;
            $action_id_str = $request->input('initial_action_id');
            $action_id_arr = explode("-", $action_id_str);
            $action_id = $action_id_arr[0];
            $action_short_code = $action_id_arr[1];

            $automatedWorkflowAction = new AutomatedWorkflowAction();
            $automatedWorkflowAction->tenant_id = $tenant_id;
            $automatedWorkflowAction->automated_workflow_initial_action_id = $action_id;
            $automatedWorkflowAction->action_name = $request->input('configure_action_name');
            $automatedWorkflowAction->action_description = $request->input('action_description');

            if ($action_short_code == '1') {
                $automatedWorkflowAction->client_sms_body = $request->input('client_sms_body');
                $automatedWorkflowAction->send_sms_choice = $request->input('send_sms_choice');
                $automatedWorkflowAction->person_field_project_type_id = $request->input('person_field_project_type_id');
                $automatedWorkflowAction->person_field_project_type_name = $request->input('person_field_project_type_name');
                $automatedWorkflowAction->person_field_project_type_section_selector = $request->input('person_field_project_type_section_selector');
                $automatedWorkflowAction->person_field_project_type_section_selector_name = $request->input('person_field_project_type_section_selector_name');
                $automatedWorkflowAction->person_field_project_type_section_field_selector = $request->input('person_field_project_type_section_field_selector');
                $automatedWorkflowAction->person_field_project_type_section_field_selector_name = $request->input('person_field_project_type_section_field_selector_name');
            } else if ($action_short_code == '3') {
                $automatedWorkflowAction->fv_project_note_body = $request->input('client_sms_body');
                $automatedWorkflowAction->fv_project_note_with_pin = $request->input('fv_project_note_with_pin') == 'on' ? 1 : 0;
            } else if ($action_short_code == '4') {
                $automatedWorkflowAction->fv_project_task_body = $request->input('client_sms_body');
                $fv_project_task_assign_type = $request->input('fv_project_task_assign_type');
                $automatedWorkflowAction->fv_project_task_assign_type = $fv_project_task_assign_type;
                if ($fv_project_task_assign_type == 'role') {
                    $automatedWorkflowAction->fv_project_task_assign_user_role = $request->input('fv_project_task_assign_user_role');
                    $automatedWorkflowAction->fv_project_task_assign_user_role_name = $request->input('fv_project_task_assign_user_role_name');
                } else {
                    $automatedWorkflowAction->fv_project_task_assign_user_id = $request->input('fv_project_task_assign_user_id');
                    $automatedWorkflowAction->fv_project_task_assign_user_name = $request->input('fv_project_task_assign_user_name');
                }
            } else if ($action_short_code == '5') {
                $automatedWorkflowAction->email_note_body = $request->input('client_sms_body');
            } else if ($action_short_code == '6') {
                $automatedWorkflowAction->fv_project_hashtag = $request->input('fv_project_hashtag');
            } else if ($action_short_code == '7') {
                $automatedWorkflowAction->fv_client_hashtag = $request->input('fv_project_hashtag');
            } else if ($action_short_code == '8') {
                $automatedWorkflowAction->section_visibility_project_type_id = $request->input('section_visibility_project_type_id');
                $automatedWorkflowAction->section_visibility_section_selector = $request->input('section_visibility_section_selector');
                $automatedWorkflowAction->section_visibility = $request->input('section_visibility');
            } else if ($action_short_code == '10') {
                $phase_assignment = $request->input('phase_assignment');
                $automatedWorkflowAction->phase_assignment = $phase_assignment;
                if ($phase_assignment == 'Specific_Phase') {
                    $automatedWorkflowAction->phase_assignment_project_type_id = $request->input('phase_assignment_project_type_id');
                    $automatedWorkflowAction->project_phase_id_native = $request->input('project_phase_id_native');
                    $automatedWorkflowAction->phase_assignment_project_type_name = $request->input('phase_assignment_project_type_name');
                    $automatedWorkflowAction->project_phase_id_native_name = $request->input('project_phase_id_native_name');
                }
            } else if ($action_short_code == '11') {
                $automatedWorkflowAction->delivery_hook_url = $request->input('delivery_hook_url');
            } else if ($action_short_code == '12') {
                $automatedWorkflowAction->email_note_body = $request->input('client_sms_body');
            } else if ($action_short_code == '13') {
                $from_field_selector_name = $request->input('mirror_from_field_project_type_section_field_selector_name');
                $to_field_selector_name = $request->input('mirror_to_field_project_type_section_field_selector_name');
                $automatedWorkflowAction->mirror_from_field_project_type_id = $request->input('mirror_from_field_project_type_id');
                $automatedWorkflowAction->mirror_from_field_project_type_name = $request->input('mirror_from_field_project_type_name');
                $automatedWorkflowAction->mirror_from_field_project_type_section_selector = $request->input('mirror_from_field_project_type_section_selector');
                $automatedWorkflowAction->mirror_from_field_project_type_section_selector_name = $request->input('mirror_from_field_project_type_section_selector_name');
                $automatedWorkflowAction->mirror_from_field_project_type_section_field_selector = $request->input('mirror_from_field_project_type_section_field_selector');
                $automatedWorkflowAction->mirror_from_field_project_type_section_field_selector_name = $from_field_selector_name;
                $automatedWorkflowAction->mirror_from_field_project_type_section_field_selector_type = substr($from_field_selector_name, (strrpos($from_field_selector_name, "(")) + 1,  -1);
                $automatedWorkflowAction->mirror_to_field_project_type_id = $request->input('mirror_to_field_project_type_id');
                $automatedWorkflowAction->mirror_to_field_project_type_name = $request->input('mirror_to_field_project_type_name');
                $automatedWorkflowAction->mirror_to_field_project_type_section_selector = $request->input('mirror_to_field_project_type_section_selector');
                $automatedWorkflowAction->mirror_to_field_project_type_section_selector_name = $request->input('mirror_to_field_project_type_section_selector_name');
                $automatedWorkflowAction->mirror_to_field_project_type_section_field_selector = $to_field_selector_name;
                $automatedWorkflowAction->mirror_to_field_project_type_section_field_selector_name = substr($to_field_selector_name, (strrpos($to_field_selector_name, "(")) + 1,  -1);
            } else if ($action_short_code == '14') {
                $automatedWorkflowAction->project_team_choice = $request->input('project_team_choice');
                $automatedWorkflowAction->team_member_user_id = $request->input('team_member_user_id');
                $automatedWorkflowAction->team_member_user_name = $request->input('team_member_user_name');
                $automatedWorkflowAction->add_team_member_choice = $request->input('add_team_member_choice');
                $automatedWorkflowAction->add_team_member_choice_level = $request->input('add_team_member_choice_level');
            }

            $automatedWorkflowAction->save();

            return redirect(url("/admin/automated_workflow"))->with('success', "Setting saved successfully!");
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     *  [POST] Automated workflow add action map
     */
    public function addActionMap(Request $request)
    {
        try {
            $tenant_id = $this->cur_tenant_id;
            $trigger_id = $request->input('map_trigger_id');
            $action_id = $request->input('map_action_id');

            $automatedWorkflowTriggerActionMapping = new AutomatedWorkflowTriggerActionMapping();
            $automatedWorkflowTriggerActionMapping->tenant_id = $tenant_id;
            $automatedWorkflowTriggerActionMapping->trigger_id = $trigger_id;
            $automatedWorkflowTriggerActionMapping->action_id = $action_id;
            $automatedWorkflowTriggerActionMapping->workflow_description = $request->input('workflow_description');
            $automatedWorkflowTriggerActionMapping->save();

            return redirect(url("/admin/automated_workflow"))->with('success', "Setting saved successfully!");
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     *  [POST] Automated workflow delete action map
     */
    public function deleteActionMap(Request $request)
    {
        try {
            $only_map = $request->input('only_map');
            if ($only_map) {
                $map_id = $request->input('map_id');
                AutomatedWorkflowTriggerActionMapping::where('id', $map_id)->delete();
            } else {
                $action_id = $request->input('action_id');
                AutomatedWorkflowAction::where('id', $action_id)->where('tenant_id', $this->cur_tenant_id)->delete();
                AutomatedWorkflowTriggerActionMapping::where('action_id', $action_id)->where('tenant_id', $this->cur_tenant_id)->delete();
            }

            return response()->json([
                'status'  => true,
                'message' => "Setting saved successfully!"
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }


    /**
     *  Custom Function to Prepare Action Body String
     */
    public function prepareActionBody($action)
    {
        $all_columns = DB::getSchemaBuilder()->getColumnListing('automated_workflow_actions');
        $ignore_columns = ['id', 'tenant_id', 'automated_workflow_initial_action_id', 'action_name', 'action_description', 'note', 'is_active', 'created_at', 'updated_at', 'fv_project_task_assign_user_id', 'person_field_project_type_section_selector', 'person_field_project_type_section_field_selector', 'mirror_from_field_project_type_section_selector', 'mirror_from_field_project_type_section_field_selector', 'mirror_to_field_project_type_section_selector', 'mirror_to_field_project_type_section_field_selector'];
        $columns = array_diff($all_columns, $ignore_columns);
        $body_str = "";
        foreach ($columns as $column) {
            if ($action->$column) {
                $body_str .= "  " . ucfirst(str_replace('_', ' ', $column)) . "=" . $action->$column . ";";
            }
        }
        return ltrim(rtrim($body_str, ';'), ' ');
    }

    /**
     *  [POST] Update action map data
     */
    public function updateActionMap(Request $request)
    {
        try {
            $map_id = $request->input('update_map_id');

            $automatedWorkflowTriggerActionMapping =  AutomatedWorkflowTriggerActionMapping::find($map_id);
            $automatedWorkflowTriggerActionMapping->status = $request->input('update_map_status');
            $automatedWorkflowTriggerActionMapping->workflow_description = $request->input('update_workflow_description');
            $automatedWorkflowTriggerActionMapping->save();

            return redirect(url("/admin/automated_workflow"))->with('success', "Setting saved successfully!");
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     *  [POST] Update action data
     */
    public function updateActionInfo(Request $request)
    {
        try {
            $action_id = $request->input('update_map_action_id');
            $action_short_code = $request->input('update_map_action_short_code');

            $automatedWorkflowAction = AutomatedWorkflowAction::where('id', $action_id)->where('tenant_id', $this->cur_tenant_id)->first();
            $automatedWorkflowAction->action_name = $request->input('update_map_action_name');
            $automatedWorkflowAction->action_description = $request->input('update_map_action_description');
            //$automatedWorkflowAction->note = $request->input('update_map_action_note');

            if ($action_short_code == '1') {
                $automatedWorkflowAction->client_sms_body = $request->input('update_client_sms_body');
                $automatedWorkflowAction->send_sms_choice = $request->input('update_send_sms_choice');
                if ($request->input('update_send_sms_choice') == 'To Project Client') {
                    $automatedWorkflowAction->person_field_project_type_id = null;
                    $automatedWorkflowAction->person_field_project_type_name = null;
                    $automatedWorkflowAction->person_field_project_type_section_selector = null;
                    $automatedWorkflowAction->person_field_project_type_section_selector_name = null;
                    $automatedWorkflowAction->person_field_project_type_section_field_selector = null;
                    $automatedWorkflowAction->person_field_project_type_section_field_selector_name = null;
                } else {
                    $automatedWorkflowAction->person_field_project_type_id = $request->input('update_person_field_project_type_id');
                    $automatedWorkflowAction->person_field_project_type_name = $request->input('update_person_field_project_type_name');
                    $automatedWorkflowAction->person_field_project_type_section_selector = $request->input('update_person_field_project_type_section_selector');
                    $automatedWorkflowAction->person_field_project_type_section_selector_name = $request->input('update_person_field_project_type_section_selector_name');
                    $automatedWorkflowAction->person_field_project_type_section_field_selector = $request->input('update_person_field_project_type_section_field_selector');
                    $automatedWorkflowAction->person_field_project_type_section_field_selector_name = $request->input('update_person_field_project_type_section_field_selector_name');
                }
            } else if ($action_short_code == '3') {
                $automatedWorkflowAction->fv_project_note_body = $request->input('update_client_sms_body');
                $automatedWorkflowAction->fv_project_note_with_pin = $request->input('update_fv_project_note_with_pin') == 'on' ? 1 : 0;
            } else if ($action_short_code == '4') {
                $automatedWorkflowAction->fv_project_task_body = $request->input('update_client_sms_body');
                $fv_project_task_assign_type = $request->input('update_fv_project_task_assign_type');
                $automatedWorkflowAction->fv_project_task_assign_type = $fv_project_task_assign_type;
                if ($fv_project_task_assign_type == 'role') {
                    $automatedWorkflowAction->fv_project_task_assign_user_role = $request->input('update_fv_project_task_assign_user_role');
                    $automatedWorkflowAction->fv_project_task_assign_user_role_name = $request->input('update_fv_project_task_assign_user_role_name');
                } else {
                    $automatedWorkflowAction->fv_project_task_assign_user_id = $request->input('update_fv_project_task_assign_user_id');
                    $automatedWorkflowAction->fv_project_task_assign_user_name = $request->input('update_fv_project_task_assign_user_name');
                }
            } else if ($action_short_code == '5') {
                $automatedWorkflowAction->email_note_body = $request->input('update_client_sms_body');
            } else if ($action_short_code == '6') {
                $automatedWorkflowAction->fv_project_hashtag = $request->input('update_fv_project_hashtag');
            } else if ($action_short_code == '7') {
                $automatedWorkflowAction->fv_client_hashtag = $request->input('update_fv_project_hashtag');
            } else if ($action_short_code == '8') {
                $automatedWorkflowAction->section_visibility_project_type_id = $request->input('update_section_visibility_project_type_id');
                $automatedWorkflowAction->section_visibility_section_selector = $request->input('update_section_visibility_section_selector');
                $automatedWorkflowAction->section_visibility = $request->input('update_section_visibility');
            } else if ($action_short_code == '10') {
                $phase_assignment = $request->input('update_phase_assignment');
                $automatedWorkflowAction->phase_assignment = $phase_assignment;
                if ($phase_assignment == 'Specific_Phase') {
                    $automatedWorkflowAction->phase_assignment_project_type_id = $request->input('update_phase_assignment_project_type_id');
                    $automatedWorkflowAction->project_phase_id_native = $request->input('update_project_phase_id_native');
                    $automatedWorkflowAction->phase_assignment_project_type_name = $request->input('update_phase_assignment_project_type_name');
                    $automatedWorkflowAction->project_phase_id_native_name = $request->input('update_project_phase_id_native_name');
                } else {
                    $automatedWorkflowAction->phase_assignment_project_type_id = null;
                    $automatedWorkflowAction->project_phase_id_native = null;
                    $automatedWorkflowAction->phase_assignment_project_type_name = null;
                    $automatedWorkflowAction->project_phase_id_native_name = null;
                }
            } else if ($action_short_code == '11') {
                $automatedWorkflowAction->delivery_hook_url = $request->input('update_delivery_hook_url');
            } else if ($action_short_code == '12') {
                $automatedWorkflowAction->email_note_body = $request->input('update_client_sms_body');
            } else if ($action_short_code == '13') {
                $from_field_selector_name = $request->input('update_mirror_from_field_project_type_section_field_selector_name');
                $to_field_selector_name = $request->input('update_mirror_to_field_project_type_section_field_selector_name');
                $automatedWorkflowAction->mirror_from_field_project_type_id = $request->input('update_mirror_from_field_project_type_id');
                $automatedWorkflowAction->mirror_from_field_project_type_name = $request->input('update_mirror_from_field_project_type_name');
                $automatedWorkflowAction->mirror_from_field_project_type_section_selector = $request->input('update_mirror_from_field_project_type_section_selector');
                $automatedWorkflowAction->mirror_from_field_project_type_section_selector_name = $request->input('update_mirror_from_field_project_type_section_selector_name');
                $automatedWorkflowAction->mirror_from_field_project_type_section_field_selector = $request->input('update_mirror_from_field_project_type_section_field_selector');
                $automatedWorkflowAction->mirror_from_field_project_type_section_field_selector_name = $from_field_selector_name;
                $automatedWorkflowAction->mirror_from_field_project_type_section_field_selector_type = substr($from_field_selector_name, (strrpos($from_field_selector_name, "(")) + 1,  -1);
                $automatedWorkflowAction->mirror_to_field_project_type_id = $request->input('update_mirror_to_field_project_type_id');
                $automatedWorkflowAction->mirror_to_field_project_type_name = $request->input('update_mirror_to_field_project_type_name');
                $automatedWorkflowAction->mirror_to_field_project_type_section_selector = $request->input('update_mirror_to_field_project_type_section_selector');
                $automatedWorkflowAction->mirror_to_field_project_type_section_selector_name = $request->input('update_mirror_to_field_project_type_section_selector_name');
                $automatedWorkflowAction->mirror_to_field_project_type_section_field_selector = $request->input('update_mirror_to_field_project_type_section_field_selector');
                $automatedWorkflowAction->mirror_to_field_project_type_section_field_selector_name = $to_field_selector_name;
                $automatedWorkflowAction->mirror_to_field_project_type_section_field_selector_type = substr($to_field_selector_name, (strrpos($to_field_selector_name, "(")) + 1,  -1);
            } else if ($action_short_code == '14') {
                $automatedWorkflowAction->project_team_choice = $request->input('update_project_team_choice');
                $automatedWorkflowAction->team_member_user_id = $request->input('update_team_member_user_id');
                $automatedWorkflowAction->team_member_user_name = $request->input('update_team_member_user_name');
                if ($request->input('update_project_team_choice') == 'Remove a Team Member') {
                    $automatedWorkflowAction->add_team_member_choice = null;
                    $automatedWorkflowAction->add_team_member_choice_level = null;
                } else {
                    $automatedWorkflowAction->add_team_member_choice = $request->input('update_add_team_member_choice');
                    if ($request->input('update_add_team_member_choice') == 'Level') {
                        $automatedWorkflowAction->add_team_member_choice_level = $request->input('update_add_team_member_choice_level');
                    } else {
                        $automatedWorkflowAction->add_team_member_choice_level = null;
                    }
                }
            }
            $automatedWorkflowAction->save();

            return redirect(url("/admin/automated_workflow"))->with('success', "Setting saved successfully!");
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     *  [GET] Get the list of Trigger & Actions
     */
    public function getTriggerActionList()
    {
        try {
            $tenant_id = $this->cur_tenant_id;

            $initial_triggers = AutomatedWorkflowTrigger::where('tenant_id', $tenant_id)->where('is_active', true)->select('id', 'trigger_name')->orderBy('id', 'ASC')->get();
            $eligible_actions = AutomatedWorkflowAction::where('tenant_id', $tenant_id)->where('is_active', true)->select('id', 'action_name')->orderBy('id', 'ASC')->get();

            return response()->json([
                'initial_triggers'  => $initial_triggers,
                'eligible_actions'  => $eligible_actions,
                'message' => "Success"
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     *  [GET] Get User Role List of FV
     */
    public function getRoleList()
    {
        try {
            $contact_metadata = collect(json_decode($this->fv_service->getContactMetadata()));
            $person_types = $contact_metadata->where('selector', 'personTypes')->first();
            $options = '<option value="">Select Role</option>';
            if (isset($person_types->allowedValues)) {
                foreach ($person_types->allowedValues as $item) {
                    $options .= '<option value="' . $item->value . '">' . $item->name . '</option>';
                }
            }
            return response()->json([
                'status'  => true,
                'html' => $options
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     *  [GET] Get Project Phase List of FV
     */
    public function getPhaseList(Request $request)
    {
        try {
            $project_type_id = $request->project_type_id;
            $fv_project_type_phase_list = json_decode($this->fv_service->getProjectTypePhaseList($project_type_id), true);
            $options = '<option value="">Choose Phase</option>';
            if (isset($fv_project_type_phase_list['items']) and !empty($fv_project_type_phase_list['items'])) {
                $phases = collect($fv_project_type_phase_list['items']);
                foreach ($phases as $key => $phase) {
                    $options .= '<option value="' . $phase['phaseId']['native'] . '">' . $phase['name'] . '</option>';
                }
            }
            return response()->json([
                'status'  => true,
                'html' => $options
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
