<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as Logging;
use Exception;

use App\Services\FilevineService;
use App\Services\TriggerActionService;

use App\Models\AutomatedWorkflowLog;
use App\Models\AutomatedWorkflowTrigger;
use App\Models\AutomatedWorkflowTriggerActionMapping;
use App\Models\Tenant;


class AutomatedWorkflowWebhookController extends Controller
{

    public function __construct()
    {
        Controller::setSubDomainName();
    }

    /**
     * [POST] Project Created Webhook of Automated Workflow
     */
    public function projectCreated($domain, Request $request)
    {
        Logging::warning(json_encode($request->all()));
        try {
            $tenant_details = Tenant::where('tenant_name', $domain)->first();
            if ($tenant_details == null) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Tenant details not found!']);
            }
            $tenant_id = $tenant_details->id;

            //Find trigger id
            $trigger_filters = AutomatedWorkflowTrigger::join('automated_workflow_trigger_filters', 'automated_workflow_triggers.id', '=', 'automated_workflow_trigger_filters.trigger_id')
                ->where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', $request->Object)
                ->where('automated_workflow_triggers.trigger_event', $request->Event)
                ->where('automated_workflow_triggers.is_filter', true)
                ->select('automated_workflow_triggers.id AS trigger_id', 'fv_project_type_id')
                ->get();
            $triggers = AutomatedWorkflowTrigger::where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', $request->Object)
                ->where('automated_workflow_triggers.trigger_event', $request->Event)
                ->where('automated_workflow_triggers.is_filter', false)
                ->select('automated_workflow_triggers.id AS trigger_id')
                ->pluck('trigger_id')->toArray();

            if (!count($trigger_filters) && !count($triggers)) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Trigger not found for this request!']);
            }

            $trigger_filter_ids = [];
            if (count($trigger_filters)) {
                $Tenant = Tenant::find($tenant_id);
                $apiurl = config('services.fv.default_api_base_url');
                if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                    $apiurl = $Tenant->fv_api_base_url;
                }
                $fv_service = new FilevineService($apiurl, "");
                $projectId = $request->ObjectId['ProjectId'];
                $project_details = json_decode($fv_service->getProjectsById($projectId));
                $projectTypeId = isset($project_details->projectTypeId->native) ? $project_details->projectTypeId->native : 0;
                foreach ($trigger_filters as $trigger) {
                    if ($trigger->fv_project_type_id == $projectTypeId) {
                        $trigger_filter_ids[] = $trigger->trigger_id;
                    }
                }
            }

            $trigger_ids = array_merge($trigger_filter_ids, $triggers);
            if (empty($trigger_ids)) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Trigger not found for this request!']);
            }
            foreach ($trigger_ids as $trigger_id) {
                $log_id = AutomatedWorkflowLog::saveLog($tenant_id, $trigger_id, json_encode($request->all()));
                $actions = AutomatedWorkflowTriggerActionMapping::getActionDetails($tenant_id, $trigger_id);
                foreach ($actions as $action) {
                    TriggerActionService::callActionService($action, $log_id);
                }
            }

            return response()->json(['status'  => true, 'code' => 200, 'message' => 'Request Successfully Completed!']);
        } catch (Exception $e) {
            Logging::warning(json_encode($e));
            return $e->getMessage();
        }
    }

    /**
     * [POST] Project Updated Webhook of Automated Workflow
     */
    public function projectUpdated($domain, Request $request)
    {

        Logging::warning(json_encode($request->all()));
        try {
            $tenant_details = Tenant::where('tenant_name', $domain)->first();
            if ($tenant_details == null) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Tenant details not found!']);
            }
            $tenant_id = $tenant_details->id;

            $request_action = ($request->Other['Action'] == 'ReplacedHashtags') ? 'AddedHashtag' : $request->Other['Action'];
            //Find trigger id
            $trigger_filters = AutomatedWorkflowTrigger::join('automated_workflow_trigger_filters', 'automated_workflow_triggers.id', '=', 'automated_workflow_trigger_filters.trigger_id')
                ->where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', $request->Object)
                ->where('automated_workflow_triggers.trigger_event', $request_action)
                ->where('automated_workflow_triggers.is_filter', true)
                ->select('automated_workflow_triggers.id AS trigger_id', 'fv_project_hashtag')
                ->get();
            $triggers = AutomatedWorkflowTrigger::where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', $request->Object)
                ->where('automated_workflow_triggers.trigger_event', $request_action)
                ->where('automated_workflow_triggers.is_filter', false)
                ->select('automated_workflow_triggers.id AS trigger_id')
                ->pluck('trigger_id')->toArray();

            if (!count($trigger_filters) && !count($triggers)) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Trigger not found for this request!']);
            }

            $Tenant = Tenant::find($tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $fv_service = new FilevineService($apiurl, "");
            $projectId = $request->ObjectId['ProjectId'];
            $project_details = json_decode($fv_service->getProjectsById($projectId));
            $project_hashtags = $project_details->hashtags;

            $trigger_filter_ids = [];
            foreach ($trigger_filters as $trigger) {
                $fv_project_hashtag = $trigger->fv_project_hashtag;
                foreach ($project_hashtags as $hashtag) {
                    if (strpos(strtolower($hashtag), strtolower($fv_project_hashtag)) !== false) {
                        $trigger_filter_ids[] = $trigger->trigger_id;
                    }
                }
            }

            $trigger_ids = array_merge($trigger_filter_ids, $triggers);
            if (empty($trigger_ids)) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Trigger not found for this request!']);
            }
            foreach ($trigger_ids as $trigger_id) {
                $log_id = AutomatedWorkflowLog::saveLog($tenant_id, $trigger_id, json_encode($request->all()));
                $actions = AutomatedWorkflowTriggerActionMapping::getActionDetails($tenant_id, $trigger_id);
                foreach ($actions as $action) {
                    TriggerActionService::callActionService($action, $log_id);
                }
            }

            return response()->json(['status'  => true, 'code' => 200, 'message' => 'Request Successfully Completed!']);
        } catch (Exception $e) {
            Logging::warning(json_encode($e));
            return $e->getMessage();
        }
    }



    /**
     * [POST] Project PhaseChanged Webhook of Automated Workflow
     */
    public function projectPhaseChanged($domain, Request $request)
    {
        Logging::warning(json_encode($request->all()));
        try {
            $tenant_details = Tenant::where('tenant_name', $domain)->first();
            if ($tenant_details == null) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Tenant details not found!']);
            }
            $tenant_id = $tenant_details->id;

            //Find trigger id
            $trigger_filters = AutomatedWorkflowTrigger::join('automated_workflow_trigger_filters', 'automated_workflow_triggers.id', '=', 'automated_workflow_trigger_filters.trigger_id')
                ->where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', $request->Object)
                ->where('automated_workflow_triggers.trigger_event', $request->Event)
                ->where('automated_workflow_triggers.is_filter', true)
                ->where('automated_workflow_trigger_filters.fv_project_type_id', $request->ObjectId['ProjectTypeId'])
                ->where('automated_workflow_trigger_filters.fv_project_phase_id', $request->ObjectId['PhaseId'])
                ->select('automated_workflow_triggers.id AS trigger_id')
                ->pluck('trigger_id')->toArray();

            $triggers = AutomatedWorkflowTrigger::where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', $request->Object)
                ->where('automated_workflow_triggers.trigger_event', $request->Event)
                ->where('automated_workflow_triggers.is_filter', false)
                ->select('automated_workflow_triggers.id AS trigger_id')
                ->pluck('trigger_id')->toArray();

            $trigger_ids = array_merge($trigger_filters, $triggers);
            if (empty($trigger_ids)) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Trigger not found for this request!']);
            }
            foreach ($trigger_ids as $trigger_id) {
                $log_id = AutomatedWorkflowLog::saveLog($tenant_id, $trigger_id, json_encode($request->all()));
                $actions = AutomatedWorkflowTriggerActionMapping::getActionDetails($tenant_id, $trigger_id);
                foreach ($actions as $action) {
                    TriggerActionService::callActionService($action, $log_id);
                }
            }

            return response()->json(['status'  => true, 'code' => 200, 'message' => 'Request Successfully Completed!']);
        } catch (Exception $e) {
            Logging::warning(json_encode($e));
            return $e->getMessage();
        }
    }

    /**
     * [POST] Project Related Webhook of Automated Workflow
     */
    public function projectRelated($domain, Request $request)
    {

        Logging::warning(json_encode($request->all()));
        try {
            $tenant_details = Tenant::where('tenant_name', $domain)->first();
            if ($tenant_details == null) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Tenant details not found!']);
            }
            $tenant_id = $tenant_details->id;

            //Find trigger id
            $triggers = AutomatedWorkflowTrigger::where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', $request->Object . 'Relation')
                ->where('automated_workflow_triggers.trigger_event', $request->Event)
                ->where('automated_workflow_triggers.is_filter', false)
                ->select('automated_workflow_triggers.id AS trigger_id')
                ->pluck('trigger_id')->toArray();

            $trigger_ids = $triggers;
            if (empty($trigger_ids)) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Trigger not found for this request!']);
            }
            foreach ($trigger_ids as $trigger_id) {
                $log_id = AutomatedWorkflowLog::saveLog($tenant_id, $trigger_id, json_encode($request->all()));
                $actions = AutomatedWorkflowTriggerActionMapping::getActionDetails($tenant_id, $trigger_id);
                foreach ($actions as $action) {
                    TriggerActionService::callActionService($action, $log_id);
                }
            }

            return response()->json(['status'  => true, 'code' => 200, 'message' => 'Request Successfully Completed!']);
        } catch (Exception $e) {
            Logging::warning(json_encode($e));
            return $e->getMessage();
        }
    }

    /**
     * [POST] Project Unrelated Webhook of Automated Workflow
     */
    public function projectUnrelated($domain, Request $request)
    {

        Logging::warning(json_encode($request->all()));
        try {
            $tenant_details = Tenant::where('tenant_name', $domain)->first();
            if ($tenant_details == null) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Tenant details not found!']);
            }
            $tenant_id = $tenant_details->id;

            //Find trigger id
            $triggers = AutomatedWorkflowTrigger::where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', $request->Object . 'Relation')
                ->where('automated_workflow_triggers.trigger_event', $request->Event)
                ->where('automated_workflow_triggers.is_filter', false)
                ->select('automated_workflow_triggers.id AS trigger_id')
                ->pluck('trigger_id')->toArray();

            $trigger_ids = $triggers;
            if (empty($trigger_ids)) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Trigger not found for this request!']);
            }
            foreach ($trigger_ids as $trigger_id) {
                $log_id = AutomatedWorkflowLog::saveLog($tenant_id, $trigger_id, json_encode($request->all()));
                $actions = AutomatedWorkflowTriggerActionMapping::getActionDetails($tenant_id, $trigger_id);
                foreach ($actions as $action) {
                    TriggerActionService::callActionService($action, $log_id);
                }
            }

            return response()->json(['status'  => true, 'code' => 200, 'message' => 'Request Successfully Completed!']);
        } catch (Exception $e) {
            Logging::warning(json_encode($e));
            return $e->getMessage();
        }
    }

    /**
     * [POST] Contact Created Webhook of Automated Workflow
     */
    public function contactCreated($domain, Request $request)
    {
        Logging::warning(json_encode($request->all()));
        try {
            $tenant_details = Tenant::where('tenant_name', $domain)->first();
            if ($tenant_details == null) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Tenant details not found!']);
            }
            $tenant_id = $tenant_details->id;

            //Find trigger id
            $trigger_filters = AutomatedWorkflowTrigger::join('automated_workflow_trigger_filters', 'automated_workflow_triggers.id', '=', 'automated_workflow_trigger_filters.trigger_id')
                ->where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', $request->Object)
                ->where('automated_workflow_triggers.trigger_event', $request->Event)
                ->where('automated_workflow_triggers.is_filter', true)
                ->select('automated_workflow_triggers.id AS trigger_id', 'fv_contact_person_type_name')
                ->get();

            $triggers = AutomatedWorkflowTrigger::where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', $request->Object)
                ->where('automated_workflow_triggers.trigger_event', $request->Event)
                ->where('automated_workflow_triggers.is_filter', false)
                ->select('automated_workflow_triggers.id AS trigger_id')
                ->pluck('trigger_id')->toArray();

            if (!count($trigger_filters) && !count($triggers)) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Trigger not found for this request!']);
            }

            $Tenant = Tenant::find($tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $fv_service = new FilevineService($apiurl, "");
            $PersonId = $request->ObjectId['PersonId'];
            $contact_details = json_decode($fv_service->getContactByContactId($PersonId));
            $personTypes = $contact_details->personTypes;

            $trigger_filter_ids = [];
            foreach ($trigger_filters as $trigger) {
                $fv_contact_person_type_name = $trigger->fv_contact_person_type_name;
                if (in_array($fv_contact_person_type_name, $personTypes)) {
                    $trigger_filter_ids[] = $trigger->trigger_id;
                }
            }

            $trigger_ids = array_merge($trigger_filter_ids, $triggers);
            if (empty($trigger_ids)) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Trigger not found for this request!']);
            }
            foreach ($trigger_ids as $trigger_id) {
                $log_id = AutomatedWorkflowLog::saveLog($tenant_id, $trigger_id, json_encode($request->all()));
                $actions = AutomatedWorkflowTriggerActionMapping::getActionDetails($tenant_id, $trigger_id);
                foreach ($actions as $action) {
                    TriggerActionService::callActionService($action, $log_id);
                }
            }

            return response()->json(['status'  => true, 'code' => 200, 'message' => 'Request Successfully Completed!']);
        } catch (Exception $e) {
            Logging::warning(json_encode($e));
            return $e->getMessage();
        }
    }

    /**
     * [POST] Contact Updated Webhook of Automated Workflow
     */
    public function contactUpdated($domain, Request $request)
    {
        Logging::warning(json_encode($request->all()));
        try {
            $tenant_details = Tenant::where('tenant_name', $domain)->first();
            if ($tenant_details == null) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Tenant details not found!']);
            }
            $tenant_id = $tenant_details->id;

            //Find trigger id
            $trigger_filters = AutomatedWorkflowTrigger::join('automated_workflow_trigger_filters', 'automated_workflow_triggers.id', '=', 'automated_workflow_trigger_filters.trigger_id')
                ->where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', $request->Object)
                ->where('automated_workflow_triggers.trigger_event', $request->Event)
                ->where('automated_workflow_triggers.is_filter', true)
                ->select('automated_workflow_triggers.id AS trigger_id', 'fv_contact_person_type_name')
                ->get();

            $triggers = AutomatedWorkflowTrigger::where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', $request->Object)
                ->where('automated_workflow_triggers.trigger_event', $request->Event)
                ->where('automated_workflow_triggers.is_filter', false)
                ->select('automated_workflow_triggers.id AS trigger_id')
                ->pluck('trigger_id')->toArray();

            if (!count($trigger_filters) && !count($triggers)) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Trigger not found for this request!']);
            }

            $Tenant = Tenant::find($tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $fv_service = new FilevineService($apiurl, "");
            $PersonId = $request->ObjectId['PersonId'];
            $contact_details = json_decode($fv_service->getContactByContactId($PersonId));
            $personTypes = $contact_details->personTypes;

            $trigger_filter_ids = [];
            foreach ($trigger_filters as $trigger) {
                $fv_contact_person_type_name = $trigger->fv_contact_person_type_name;
                if (in_array($fv_contact_person_type_name, $personTypes)) {
                    $trigger_filter_ids[] = $trigger->trigger_id;
                }
            }

            $trigger_ids = array_merge($trigger_filter_ids, $triggers);
            if (empty($trigger_ids)) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Trigger not found for this request!']);
            }
            foreach ($trigger_ids as $trigger_id) {
                $log_id = AutomatedWorkflowLog::saveLog($tenant_id, $trigger_id, json_encode($request->all()));
                $actions = AutomatedWorkflowTriggerActionMapping::getActionDetails($tenant_id, $trigger_id);
                foreach ($actions as $action) {
                    TriggerActionService::callActionService($action, $log_id);
                }
            }

            return response()->json(['status'  => true, 'code' => 200, 'message' => 'Request Successfully Completed!']);
        } catch (Exception $e) {
            Logging::warning(json_encode($e));
            return $e->getMessage();
        }
    }

    /**
     * [POST] Note/Task Created Webhook of Automated Workflow
     */
    public function noteCreated($domain, Request $request)
    {
        Logging::warning(json_encode($request->all()));
        try {
            $tenant_details = Tenant::where('tenant_name', $domain)->first();
            if ($tenant_details == null) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Tenant details not found!']);
            }
            $tenant_id = $tenant_details->id;

            //Find trigger id
            $trigger_filters = AutomatedWorkflowTrigger::join('automated_workflow_trigger_filters', 'automated_workflow_triggers.id', '=', 'automated_workflow_trigger_filters.trigger_id')
                ->where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', $request->Object)
                ->where('automated_workflow_triggers.trigger_event', $request->Event)
                ->where('automated_workflow_triggers.is_filter', true)
                ->select('automated_workflow_triggers.id AS trigger_id', 'fv_task_filter_type_name', 'fv_task_hashtag', 'fv_task_assigned_user_id', 'fv_task_created_user_id')
                ->get();

            $triggers = AutomatedWorkflowTrigger::where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', $request->Object)
                ->where('automated_workflow_triggers.trigger_event', $request->Event)
                ->where('automated_workflow_triggers.is_filter', false)
                ->select('automated_workflow_triggers.id AS trigger_id')
                ->pluck('trigger_id')->toArray();

            if (!count($trigger_filters) && !count($triggers)) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Trigger not found for this request!']);
            }

            $Tenant = Tenant::find($tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $fv_service = new FilevineService($apiurl, "");
            $noteId = $request->ObjectId['NoteId'];
            $note_details = json_decode($fv_service->getNoteById($noteId));
            $created_user_id = isset($note_details->authorId) ? $note_details->authorId->native : 0;
            $assigned_user_id = isset($note_details->assigneeId) ? $note_details->assigneeId->native : 0;
            $note_body = isset($note_details->body) ? $note_details->body : '';

            $trigger_filter_ids = [];
            foreach ($trigger_filters as $trigger) {
                $filter_type = $trigger->fv_task_filter_type_name;
                if ($filter_type == 'Task Hashtags') {
                    if (strpos(strtolower($note_body), strtolower($trigger->fv_task_hashtag)) !== false) {
                        $trigger_filter_ids[] = $trigger->trigger_id;
                        // Execute Actions By Mapping
                    }
                } else if ($filter_type == 'Auto-Generated Task') {
                    if (strpos(strtolower($note_body), strtolower($trigger->fv_task_hashtag)) !== false) {
                        $trigger_filter_ids[] = $trigger->trigger_id;
                        // Execute Actions By Mapping
                    }
                } else if ($filter_type == 'Created By') {
                    if ($created_user_id == $trigger->fv_task_created_user_id) {
                        $trigger_filter_ids[] = $trigger->trigger_id;
                        // Execute Actions By Mapping
                    }
                } else if ($filter_type == 'Assigned To') {
                    if ($assigned_user_id == $trigger->fv_task_assigned_user_id) {
                        $trigger_filter_ids[] = $trigger->trigger_id;
                        // Execute Actions By Mapping
                    }
                }
            }

            $trigger_ids = array_merge($trigger_filter_ids, $triggers);
            if (empty($trigger_ids)) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Trigger not found for this request!']);
            }
            foreach ($trigger_ids as $trigger_id) {
                $log_id = AutomatedWorkflowLog::saveLog($tenant_id, $trigger_id, json_encode($request->all()));
                $actions = AutomatedWorkflowTriggerActionMapping::getActionDetails($tenant_id, $trigger_id);
                foreach ($actions as $action) {
                    TriggerActionService::callActionService($action, $log_id);
                }
            }

            return response()->json(['status'  => true, 'code' => 200, 'message' => 'Request Successfully Completed!']);
        } catch (Exception $e) {
            Logging::warning(json_encode($e));
            return $e->getMessage();
        }
    }

    /**
     * [POST] Note/Task Completed Webhook of Automated Workflow
     */
    public function noteCompleted($domain, Request $request)
    {
        Logging::warning(json_encode($request->all()));
        try {
            $tenant_details = Tenant::where('tenant_name', $domain)->first();
            if ($tenant_details == null) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Tenant details not found!']);
            }
            $tenant_id = $tenant_details->id;

            //Find trigger id
            $trigger_filters = AutomatedWorkflowTrigger::join('automated_workflow_trigger_filters', 'automated_workflow_triggers.id', '=', 'automated_workflow_trigger_filters.trigger_id')
                ->where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', $request->Object)
                ->where('automated_workflow_triggers.trigger_event', $request->Event)
                ->where('automated_workflow_triggers.is_filter', true)
                ->select('automated_workflow_triggers.id AS trigger_id', 'fv_task_filter_type_name', 'fv_task_hashtag', 'fv_task_completed_user_id')
                ->get();

            $triggers = AutomatedWorkflowTrigger::where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', $request->Object)
                ->where('automated_workflow_triggers.trigger_event', $request->Event)
                ->where('automated_workflow_triggers.is_filter', false)
                ->select('automated_workflow_triggers.id AS trigger_id')
                ->pluck('trigger_id')->toArray();

            if (!count($trigger_filters) && !count($triggers)) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Trigger not found for this request!']);
            }

            $Tenant = Tenant::find($tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $fv_service = new FilevineService($apiurl, "");
            $noteId = $request->ObjectId['NoteId'];
            $note_details = json_decode($fv_service->getNoteById($noteId));
            $note_body = isset($note_details->body) ? $note_details->body : '';
            $completer_user_id = isset($request->Other['CompleterId']) ? $request->Other['CompleterId'] : 0;

            $trigger_filter_ids = [];
            foreach ($trigger_filters as $trigger) {
                $filter_type = $trigger->fv_task_filter_type_name;
                if ($filter_type == 'Task Hashtags') {
                    if (strpos(strtolower($note_body), strtolower($trigger->fv_task_hashtag)) !== false) {
                        $trigger_filter_ids[] = $trigger->trigger_id;
                        // Execute Actions By Mapping
                    }
                } else if ($filter_type == 'Auto-Generated Task') {
                    if (strpos(strtolower($note_body), strtolower($trigger->fv_task_hashtag)) !== false) {
                        $trigger_filter_ids[] = $trigger->trigger_id;
                        // Execute Actions By Mapping
                    }
                } else if ($filter_type == 'Completed By') {
                    if ($completer_user_id == $trigger->fv_task_completed_user_id) {
                        $trigger_filter_ids[] = $trigger->trigger_id;
                        // Execute Actions By Mapping
                    }
                }
            }

            $trigger_ids = array_merge($trigger_filter_ids, $triggers);
            if (empty($trigger_ids)) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Trigger not found for this request!']);
            }

            foreach ($trigger_ids as $trigger_id) {
                $log_id = AutomatedWorkflowLog::saveLog($tenant_id, $trigger_id, json_encode($request->all()));
                $actions = AutomatedWorkflowTriggerActionMapping::getActionDetails($tenant_id, $trigger_id);
                foreach ($actions as $action) {
                    TriggerActionService::callActionService($action, $log_id);
                }
            }

            return response()->json(['status'  => true, 'code' => 200, 'message' => 'Request Successfully Completed!']);
        } catch (Exception $e) {
            Logging::warning(json_encode($e));
            return $e->getMessage();
        }
    }

    /**
     * [POST] CollectionItem Created Webhook of Automated Workflow
     */
    public function collectionItemCreated($domain, Request $request)
    {

        Logging::warning(json_encode($request->all()));
        try {
            $tenant_details = Tenant::where('tenant_name', $domain)->first();
            if ($tenant_details == null) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Tenant details not found!']);
            }
            $tenant_id = $tenant_details->id;

            //Find trigger id
            $trigger_filters = AutomatedWorkflowTrigger::join('automated_workflow_trigger_filters', 'automated_workflow_triggers.id', '=', 'automated_workflow_trigger_filters.trigger_id')
                ->where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', $request->Object)
                ->where('automated_workflow_triggers.trigger_event', $request->Event)
                ->where('automated_workflow_triggers.is_filter', true)
                ->where('automated_workflow_trigger_filters.fv_collection_item_project_type_id', $request->ObjectId['ProjectTypeId'])
                ->where('automated_workflow_trigger_filters.fv_collection_item_section_id', $request->ObjectId['SectionSelector'])
                ->select('automated_workflow_triggers.id AS trigger_id')
                ->pluck('trigger_id')->toArray();

            $triggers = AutomatedWorkflowTrigger::where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', $request->Object)
                ->where('automated_workflow_triggers.trigger_event', $request->Event)
                ->where('automated_workflow_triggers.is_filter', false)
                ->select('automated_workflow_triggers.id AS trigger_id')
                ->pluck('trigger_id')->toArray();

            $trigger_ids = array_merge($trigger_filters, $triggers);
            if (empty($trigger_ids)) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Trigger not found for this request!']);
            }
            foreach ($trigger_ids as $trigger_id) {
                $log_id = AutomatedWorkflowLog::saveLog($tenant_id, $trigger_id, json_encode($request->all()));
                $actions = AutomatedWorkflowTriggerActionMapping::getActionDetails($tenant_id, $trigger_id);
                foreach ($actions as $action) {
                    TriggerActionService::callActionService($action, $log_id);
                }
            }

            return response()->json(['status'  => true, 'code' => 200, 'message' => 'Request Successfully Completed!']);
        } catch (Exception $e) {
            Logging::warning(json_encode($e));
            return $e->getMessage();
        }
    }

    /**
     * [POST] CollectionItem Updated Webhook of Automated Workflow
     */
    public function collectionItemDeleted($domain, Request $request)
    {
        Logging::warning(json_encode($request->all()));
        try {
            $tenant_details = Tenant::where('tenant_name', $domain)->first();
            if ($tenant_details == null) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Tenant details not found!']);
            }
            $tenant_id = $tenant_details->id;

            //Find trigger id
            $triggers = AutomatedWorkflowTrigger::join('automated_workflow_trigger_filters', 'automated_workflow_triggers.id', '=', 'automated_workflow_trigger_filters.trigger_id')
                ->where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', $request->Object)
                ->where('automated_workflow_triggers.trigger_event', $request->Event)
                ->where('automated_workflow_triggers.is_filter', true)
                ->where('automated_workflow_trigger_filters.fv_collection_item_project_type_id', $request->ObjectId['ProjectTypeId'])
                ->where('automated_workflow_trigger_filters.fv_collection_item_section_id', $request->ObjectId['SectionSelector'])
                ->select('automated_workflow_triggers.id AS trigger_id')
                ->pluck('trigger_id')->toArray();

            $trigger_ids = $triggers;
            if (empty($trigger_ids)) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Trigger not found for this request!']);
            }
            foreach ($trigger_ids as $trigger_id) {
                $log_id = AutomatedWorkflowLog::saveLog($tenant_id, $trigger_id, json_encode($request->all()));
                $actions = AutomatedWorkflowTriggerActionMapping::getActionDetails($tenant_id, $trigger_id);
                foreach ($actions as $action) {
                    TriggerActionService::callActionService($action, $log_id);
                }
            }

            return response()->json(['status'  => true, 'code' => 200, 'message' => 'Request Successfully Completed!']);
        } catch (Exception $e) {
            Logging::warning(json_encode($e));
            return $e->getMessage();
        }
    }

    /**
     * [POST] Appointment Created Webhook of Automated Workflow
     */
    public function appointmentCreated($domain, Request $request)
    {
        Logging::warning(json_encode($request->all()));
        try {
            $tenant_details = Tenant::where('tenant_name', $domain)->first();
            if ($tenant_details == null) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Tenant details not found!']);
            }
            $tenant_id = $tenant_details->id;

            //Find trigger id
            $trigger_filters = AutomatedWorkflowTrigger::join('automated_workflow_trigger_filters', 'automated_workflow_triggers.id', '=', 'automated_workflow_trigger_filters.trigger_id')
                ->where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', $request->Object)
                ->where('automated_workflow_triggers.trigger_event', $request->Event)
                ->where('automated_workflow_triggers.is_filter', true)
                ->select('automated_workflow_triggers.id AS trigger_id', 'fv_calendar_filter_type_name', 'fv_calendar_hashtag', 'fv_calendar_attendee_user_id')
                ->get();
            $triggers = AutomatedWorkflowTrigger::where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', $request->Object)
                ->where('automated_workflow_triggers.trigger_event', $request->Event)
                ->where('automated_workflow_triggers.is_filter', false)
                ->select('automated_workflow_triggers.id AS trigger_id')
                ->pluck('trigger_id')->toArray();

            if (!count($trigger_filters) && !count($triggers)) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Trigger not found for this request!']);
            }

            $Tenant = Tenant::find($tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $fv_service = new FilevineService($apiurl, "");
            $appointmentId = $request->ObjectId['AppointmentId'];
            $appointment_details = json_decode($fv_service->getAppointments($appointmentId));

            $all_day = $appointment_details->allDay;
            $notes = $appointment_details->notes;
            $attendees = [];
            foreach ($appointment_details->attendees as $attendee) {
                $attendees[] = $attendee->userId->native;
            }

            $trigger_filter_ids = [];
            foreach ($trigger_filters as $trigger) {
                $filter_type = $trigger->fv_calendar_filter_type_name;
                if ($filter_type == 'Note Hashtag') {
                    if (strpos(strtolower($notes), strtolower($trigger->fv_calendar_hashtag)) !== false) {
                        $trigger_filter_ids[] = $trigger->trigger_id;
                        // Execute Actions By Mapping
                    }
                } else if ($filter_type == 'All Day Appointment') {
                    if ($all_day) {
                        $trigger_filter_ids[] = $trigger->trigger_id;
                        // Execute Actions By Mapping
                    }
                } else if ($filter_type == 'Attendee') {
                    if (in_array($trigger->fv_calendar_attendee_user_id, $attendees)) {
                        $trigger_filter_ids[] = $trigger->trigger_id;
                        // Execute Actions By Mapping
                    }
                }
            }

            $trigger_ids = array_merge($trigger_filter_ids, $triggers);
            if (empty($trigger_ids)) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Trigger not found for this request!']);
            }
            foreach ($trigger_ids as $trigger_id) {
                $log_id = AutomatedWorkflowLog::saveLog($tenant_id, $trigger_id, json_encode($request->all()));
                $actions = AutomatedWorkflowTriggerActionMapping::getActionDetails($tenant_id, $trigger_id);
                foreach ($actions as $action) {
                    TriggerActionService::callActionService($action, $log_id);
                }
            }

            return response()->json(['status'  => true, 'code' => 200, 'message' => 'Request Successfully Completed!']);
        } catch (Exception $e) {
            Logging::warning(json_encode($e));
            return $e->getMessage();
        }
    }

    /**
     * [POST] Appointment Updated Webhook of Automated Workflow
     */
    public function appointmentUpdated($domain, Request $request)
    {
        Logging::warning(json_encode($request->all()));
        try {
            $tenant_details = Tenant::where('tenant_name', $domain)->first();
            if ($tenant_details == null) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Tenant details not found!']);
            }
            $tenant_id = $tenant_details->id;

            //Find trigger id
            $trigger_filters = AutomatedWorkflowTrigger::join('automated_workflow_trigger_filters', 'automated_workflow_triggers.id', '=', 'automated_workflow_trigger_filters.trigger_id')
                ->where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', $request->Object)
                ->where('automated_workflow_triggers.trigger_event', $request->Event)
                ->where('automated_workflow_triggers.is_filter', true)
                ->select('automated_workflow_triggers.id AS trigger_id', 'fv_calendar_filter_type_name', 'fv_calendar_hashtag', 'fv_calendar_attendee_user_id')
                ->get();
            $triggers = AutomatedWorkflowTrigger::where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', $request->Object)
                ->where('automated_workflow_triggers.trigger_event', $request->Event)
                ->where('automated_workflow_triggers.is_filter', false)
                ->select('automated_workflow_triggers.id AS trigger_id')
                ->pluck('trigger_id')->toArray();

            if (!count($trigger_filters) && !count($triggers)) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Trigger not found for this request!']);
            }

            $Tenant = Tenant::find($tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $fv_service = new FilevineService($apiurl, "");
            $appointmentId = $request->ObjectId['AppointmentId'];
            $appointment_details = json_decode($fv_service->getAppointments($appointmentId));

            $all_day = $appointment_details->allDay;
            $notes = $appointment_details->notes;
            $attendees = [];
            foreach ($appointment_details->attendees as $attendee) {
                $attendees[] = $attendee->userId->native;
            }

            $trigger_filter_ids = [];
            foreach ($trigger_filters as $trigger) {
                $filter_type = $trigger->fv_calendar_filter_type_name;
                if ($filter_type == 'Note Hashtag') {
                    if (strpos(strtolower($notes), strtolower($trigger->fv_calendar_hashtag)) !== false) {
                        $trigger_filter_ids[] = $trigger->trigger_id;
                        // Execute Actions By Mapping
                    }
                } else if ($filter_type == 'All Day Appointment') {
                    if ($all_day) {
                        $trigger_filter_ids[] = $trigger->trigger_id;
                        // Execute Actions By Mapping
                    }
                } else if ($filter_type == 'Attendee') {
                    if (in_array($trigger->fv_calendar_attendee_user_id, $attendees)) {
                        $trigger_filter_ids[] = $trigger->trigger_id;
                        // Execute Actions By Mapping
                    }
                }
            }

            $trigger_ids = array_merge($trigger_filter_ids, $triggers);
            if (empty($trigger_ids)) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Trigger not found for this request!']);
            }
            foreach ($trigger_ids as $trigger_id) {
                $log_id = AutomatedWorkflowLog::saveLog($tenant_id, $trigger_id, json_encode($request->all()));
                $actions = AutomatedWorkflowTriggerActionMapping::getActionDetails($tenant_id, $trigger_id);
                foreach ($actions as $action) {
                    TriggerActionService::callActionService($action, $log_id);
                }
            }

            return response()->json(['status'  => true, 'code' => 200, 'message' => 'Request Successfully Completed!']);
        } catch (Exception $e) {
            Logging::warning(json_encode($e));
            return $e->getMessage();
        }
    }

    /**
     * [POST] Appointment Deleted Webhook of Automated Workflow
     */
    public function appointmentDeleted($domain, Request $request)
    {
        Logging::warning(json_encode($request->all()));
        try {
            $tenant_details = Tenant::where('tenant_name', $domain)->first();
            if ($tenant_details == null) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Tenant details not found!']);
            }
            $tenant_id = $tenant_details->id;

            //Find trigger id
            $trigger_filters = AutomatedWorkflowTrigger::join('automated_workflow_trigger_filters', 'automated_workflow_triggers.id', '=', 'automated_workflow_trigger_filters.trigger_id')
                ->where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', $request->Object)
                ->where('automated_workflow_triggers.trigger_event', $request->Event)
                ->where('automated_workflow_triggers.is_filter', true)
                ->select('automated_workflow_triggers.id AS trigger_id', 'fv_calendar_filter_type_name', 'fv_calendar_hashtag', 'fv_calendar_attendee_user_id')
                ->get();
            $triggers = AutomatedWorkflowTrigger::where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', $request->Object)
                ->where('automated_workflow_triggers.trigger_event', $request->Event)
                ->where('automated_workflow_triggers.is_filter', false)
                ->select('automated_workflow_triggers.id AS trigger_id')
                ->pluck('trigger_id')->toArray();

            if (!count($trigger_filters) && !count($triggers)) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Trigger not found for this request!']);
            }

            $Tenant = Tenant::find($tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $fv_service = new FilevineService($apiurl, "");
            $appointmentId = $request->ObjectId['AppointmentId'];
            $appointment_details = json_decode($fv_service->getAppointments($appointmentId));

            $all_day = $appointment_details->allDay;
            $notes = $appointment_details->notes;
            $attendees = [];
            foreach ($appointment_details->attendees as $attendee) {
                $attendees[] = $attendee->userId->native;
            }

            $trigger_filter_ids = [];
            foreach ($trigger_filters as $trigger) {
                $filter_type = $trigger->fv_calendar_filter_type_name;
                if ($filter_type == 'Note Hashtag') {
                    if (strpos(strtolower($notes), strtolower($trigger->fv_calendar_hashtag)) !== false) {
                        $trigger_filter_ids[] = $trigger->trigger_id;
                        // Execute Actions By Mapping
                    }
                } else if ($filter_type == 'All Day Appointment') {
                    if ($all_day) {
                        $trigger_filter_ids[] = $trigger->trigger_id;
                        // Execute Actions By Mapping
                    }
                } else if ($filter_type == 'Attendee') {
                    if (in_array($trigger->fv_calendar_attendee_user_id, $attendees)) {
                        $trigger_filter_ids[] = $trigger->trigger_id;
                        // Execute Actions By Mapping
                    }
                }
            }

            $trigger_ids = array_merge($trigger_filter_ids, $triggers);
            if (empty($trigger_ids)) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Trigger not found for this request!']);
            }
            foreach ($trigger_ids as $trigger_id) {
                $log_id = AutomatedWorkflowLog::saveLog($tenant_id, $trigger_id, json_encode($request->all()));
                $actions = AutomatedWorkflowTriggerActionMapping::getActionDetails($tenant_id, $trigger_id);
                foreach ($actions as $action) {
                    TriggerActionService::callActionService($action, $log_id);
                }
            }

            return response()->json(['status'  => true, 'code' => 200, 'message' => 'Request Successfully Completed!']);
        } catch (Exception $e) {
            Logging::warning(json_encode($e));
            return $e->getMessage();
        }
    }

    /**
     * [POST] Section Visible/Hidden Webhook of Automated Workflow
     */
    public function sectionToggle($domain, Request $request)
    {

        Logging::warning(json_encode($request->all()));
        try {
            $tenant_details = Tenant::where('tenant_name', $domain)->first();
            if ($tenant_details == null) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Tenant details not found!']);
            }
            $tenant_id = $tenant_details->id;

            //Find trigger id
            $trigger_filters = AutomatedWorkflowTrigger::join('automated_workflow_trigger_filters', 'automated_workflow_triggers.id', '=', 'automated_workflow_trigger_filters.trigger_id')
                ->where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', $request->Object)
                ->where('automated_workflow_triggers.trigger_event', $request->Other['Visibility'])
                ->where('automated_workflow_triggers.is_filter', true)
                ->where('automated_workflow_trigger_filters.fv_section_toggled_project_type_id', $request->ObjectId['ProjectTypeId'])
                ->where('automated_workflow_trigger_filters.fv_section_toggled_section_id', $request->ObjectId['SectionSelector'])
                ->select('automated_workflow_triggers.id AS trigger_id')
                ->pluck('trigger_id')->toArray();

            $triggers = AutomatedWorkflowTrigger::where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', $request->Object)
                ->where('automated_workflow_triggers.trigger_event', $request->Other['Visibility'])
                ->where('automated_workflow_triggers.is_filter', false)
                ->select('automated_workflow_triggers.id AS trigger_id')
                ->pluck('trigger_id')->toArray();

            $trigger_ids = array_merge($trigger_filters, $triggers);
            if (empty($trigger_ids)) {
                // log no matched trigger id.
                $msg = "Matched Trigger is emtpy! Object => " . $request->Object . ", Visibility => " . $request->Other['Visibility'];
                Logging::warning($msg);
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Trigger not found for this request!']);
            }
            foreach ($trigger_ids as $trigger_id) {
                $log_id = AutomatedWorkflowLog::saveLog($tenant_id, $trigger_id, json_encode($request->all()));
                $actions = AutomatedWorkflowTriggerActionMapping::getActionDetails($tenant_id, $trigger_id);
                foreach ($actions as $action) {
                    TriggerActionService::callActionService($action, $log_id);
                }
            }

            return response()->json(['status'  => true, 'code' => 200, 'message' => 'Request Successfully Completed!']);
        } catch (Exception $e) {
            Logging::warning(json_encode($e));
            return $e->getMessage();
        }
    }

    /**
     * [POST] Task Trigger Taskflow Button Trigger Executed Webhook of Automated Workflow
     */
    public function taskflowExecuted($domain, Request $request)
    {
        Logging::warning(json_encode($request->all()));
        try {
            $tenant_details = Tenant::where('tenant_name', $domain)->first();
            if ($tenant_details == null) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Tenant details not found!']);
            }
            $tenant_id = $tenant_details->id;

            //Find trigger id
            $trigger_filters = AutomatedWorkflowTrigger::join('automated_workflow_trigger_filters', 'automated_workflow_triggers.id', '=', 'automated_workflow_trigger_filters.trigger_id')
                ->where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', 'Note')
                ->where('automated_workflow_triggers.trigger_event', 'TaskflowButtonTrigger')
                ->where('automated_workflow_triggers.is_filter', true)
                ->where('automated_workflow_trigger_filters.fv_taskflow_project_type_id', $request->ObjectId['ProjectTypeId'])
                ->where('automated_workflow_trigger_filters.fv_taskflow_section_id', $request->ObjectId['SectionSelector'])
                ->where('automated_workflow_trigger_filters.fv_taskflow_field_id', $request->ObjectId['FieldSelector'])
                ->select('automated_workflow_triggers.id AS trigger_id')
                ->pluck('trigger_id')->toArray();

            $triggers = AutomatedWorkflowTrigger::where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', 'Note')
                ->where('automated_workflow_triggers.trigger_event', 'TaskflowButtonTrigger')
                ->where('automated_workflow_triggers.is_filter', false)
                ->select('automated_workflow_triggers.id AS trigger_id')
                ->pluck('trigger_id')->toArray();

            $trigger_ids = array_merge($trigger_filters, $triggers);
            if (empty($trigger_ids)) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Trigger not found for this request!']);
            }
            foreach ($trigger_ids as $trigger_id) {
                $log_id = AutomatedWorkflowLog::saveLog($tenant_id, $trigger_id, json_encode($request->all()));
                $actions = AutomatedWorkflowTriggerActionMapping::getActionDetails($tenant_id, $trigger_id);
                foreach ($actions as $action) {
                    TriggerActionService::callActionService($action, $log_id);
                }
            }

            return response()->json(['status'  => true, 'code' => 200, 'message' => 'Request Successfully Completed!']);
        } catch (Exception $e) {
            Logging::warning(json_encode($e));
            return $e->getMessage();
        }
    }

    /**
     * [POST] Task Trigger Taskflow Button Trigger Reset Webhook of Automated Workflow
     */
    public function taskflowReset($domain, Request $request)
    {
        Logging::warning(json_encode($request->all()));
        try {
            $tenant_details = Tenant::where('tenant_name', $domain)->first();
            if ($tenant_details == null) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Tenant details not found!']);
            }
            $tenant_id = $tenant_details->id;

            //Find trigger id
            $trigger_filters = AutomatedWorkflowTrigger::join('automated_workflow_trigger_filters', 'automated_workflow_triggers.id', '=', 'automated_workflow_trigger_filters.trigger_id')
                ->where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', 'Note')
                ->where('automated_workflow_triggers.trigger_event', 'TaskflowButtonTrigger')
                ->where('automated_workflow_triggers.is_filter', true)
                ->where('automated_workflow_trigger_filters.fv_taskflow_project_type_id', $request->ObjectId['ProjectTypeId'])
                ->where('automated_workflow_trigger_filters.fv_taskflow_section_id', $request->ObjectId['SectionSelector'])
                ->where('automated_workflow_trigger_filters.fv_taskflow_field_id', $request->ObjectId['FieldSelector'])
                ->select('automated_workflow_triggers.id AS trigger_id')
                ->pluck('trigger_id')->toArray();

            $triggers = AutomatedWorkflowTrigger::where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', 'Note')
                ->where('automated_workflow_triggers.trigger_event', 'TaskflowButtonTrigger')
                ->where('automated_workflow_triggers.is_filter', false)
                ->select('automated_workflow_triggers.id AS trigger_id')
                ->pluck('trigger_id')->toArray();

            $trigger_ids = array_merge($trigger_filters, $triggers);
            if (empty($trigger_ids)) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Trigger not found for this request!']);
            }
            foreach ($trigger_ids as $trigger_id) {
                $log_id = AutomatedWorkflowLog::saveLog($tenant_id, $trigger_id, json_encode($request->all()));
                $actions = AutomatedWorkflowTriggerActionMapping::getActionDetails($tenant_id, $trigger_id);
                foreach ($actions as $action) {
                    TriggerActionService::callActionService($action, $log_id);
                }
            }
            return response()->json(['status'  => true, 'code' => 200, 'message' => 'Request Successfully Completed!']);
        } catch (Exception $e) {
            Logging::warning(json_encode($e));
            return $e->getMessage();
        }
    }

    /**
     * Handle TeamMessageReply from Clinet Portal Trigger, Manually Call This Method From Client Portal
     */
    public function teamMessageReply($params)
    {
        Logging::warning(json_encode($params));
        try {
            $tenant_id = $params['tenant_id'];
            $triggers = AutomatedWorkflowTrigger::where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', 'TeamMessageReply')
                ->where('automated_workflow_triggers.is_filter', false)
                ->select('automated_workflow_triggers.id AS trigger_id')
                ->pluck('trigger_id')->toArray();

            $trigger_ids = $triggers;
            if (empty($trigger_ids)) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Trigger not found for this request!']);
            }
            foreach ($trigger_ids as $trigger_id) {
                $log_id = AutomatedWorkflowLog::saveLog($tenant_id, $trigger_id, json_encode($params));
                $actions = AutomatedWorkflowTriggerActionMapping::getActionDetails($tenant_id, $trigger_id);
                foreach ($actions as $action) {
                    TriggerActionService::callActionService($action, $log_id);
                }
            }
            return response()->json(['status'  => true, 'code' => 200, 'message' => 'Request Successfully Completed!']);
        } catch (Exception $e) {
            Logging::warning(json_encode($e));
            return $e->getMessage();
        }
    }

    /**
     * Handle DocumentUploaded from Clinet Portal Trigger, Manually Call This Method From Client Portal
     */
    public function documentUploaded($params)
    {
        Logging::warning(json_encode($params));
        try {
            $tenant_id = $params['tenant_id'];
            $triggers = AutomatedWorkflowTrigger::where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', 'DocumentUploaded')
                ->where('automated_workflow_triggers.is_filter', false)
                ->select('automated_workflow_triggers.id AS trigger_id')
                ->pluck('trigger_id')->toArray();

            $trigger_filters = AutomatedWorkflowTrigger::join('automated_workflow_trigger_filters', 'automated_workflow_triggers.id', '=', 'automated_workflow_trigger_filters.trigger_id')
                ->where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', 'DocumentUploaded')
                ->where('automated_workflow_triggers.is_filter', true)
                ->where('automated_workflow_trigger_filters.client_file_upload_configuration_id', $params['scheme_id'])
                ->select('automated_workflow_triggers.id AS trigger_id')
                ->pluck('trigger_id')->toArray();

            $trigger_ids = array_merge($trigger_filters, $triggers);
            if (empty($trigger_ids)) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Trigger not found for this request!']);
            }
            foreach ($trigger_ids as $trigger_id) {
                $log_id = AutomatedWorkflowLog::saveLog($tenant_id, $trigger_id, json_encode($params));
                $actions = AutomatedWorkflowTriggerActionMapping::getActionDetails($tenant_id, $trigger_id);
                foreach ($actions as $action) {
                    TriggerActionService::callActionService($action, $log_id);
                }
            }
            return response()->json(['status'  => true, 'code' => 200, 'message' => 'Request Successfully Completed!']);
        } catch (Exception $e) {
            Logging::warning(json_encode($e));
            return $e->getMessage();
        }
    }

    /**
     * Handle Form Submitted from Clinet Portal Trigger, Manually Call This Method From Client Portal
     */
    public function formSubmitted($params)
    {
        Logging::warning(json_encode($params));
        try {
            $tenant_id = $params['tenant_id'];
            $triggers = AutomatedWorkflowTrigger::where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', 'FormSubmitted')
                ->where('automated_workflow_triggers.is_filter', false)
                ->select('automated_workflow_triggers.id AS trigger_id')
                ->pluck('trigger_id')->toArray();

            $trigger_filters = AutomatedWorkflowTrigger::join('automated_workflow_trigger_filters', 'automated_workflow_triggers.id', '=', 'automated_workflow_trigger_filters.trigger_id')
                ->where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', 'FormSubmitted')
                ->where('automated_workflow_triggers.is_filter', true)
                ->where('automated_workflow_trigger_filters.tenant_form_id', $params['form_id'])
                ->select('automated_workflow_triggers.id AS trigger_id')
                ->pluck('trigger_id')->toArray();

            $trigger_ids = array_merge($trigger_filters, $triggers);
            if (empty($trigger_ids)) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Trigger not found for this request!']);
            }
            foreach ($trigger_ids as $trigger_id) {
                $log_id = AutomatedWorkflowLog::saveLog($tenant_id, $trigger_id, json_encode($params));
                $actions = AutomatedWorkflowTriggerActionMapping::getActionDetails($tenant_id, $trigger_id);
                foreach ($actions as $action) {
                    TriggerActionService::callActionService($action, $log_id);
                }
            }
            return response()->json(['status'  => true, 'code' => 200, 'message' => 'Request Successfully Completed!']);
        } catch (Exception $e) {
            Logging::warning(json_encode($e));
            return $e->getMessage();
        }
    }

    /**
     * Handle SMS Received Trigger, Manually Call This Method From Handle Inbound Message
     */
    public function smsReceived($params)
    {
        Logging::warning(json_encode($params));
        try {
            $tenant_id = $params['tenant_id'];
            $triggers = AutomatedWorkflowTrigger::where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', 'SMSReceived')
                ->where('automated_workflow_triggers.is_filter', false)
                ->select('automated_workflow_triggers.id AS trigger_id')
                ->pluck('trigger_id')->toArray();

            $trigger_filters = AutomatedWorkflowTrigger::join('automated_workflow_trigger_filters', 'automated_workflow_triggers.id', '=', 'automated_workflow_trigger_filters.trigger_id')
                ->where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', 'SMSReceived')
                ->where('automated_workflow_triggers.is_filter', true)
                ->where('automated_workflow_trigger_filters.sms_line', $params['sms_line'])
                ->select('automated_workflow_triggers.id AS trigger_id')
                ->pluck('trigger_id')->toArray();

            $trigger_ids = array_merge($trigger_filters, $triggers);
            if (empty($trigger_ids)) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Trigger not found for this request!']);
            }
            foreach ($trigger_ids as $trigger_id) {
                $log_id = AutomatedWorkflowLog::saveLog($tenant_id, $trigger_id, json_encode($params));
                $actions = AutomatedWorkflowTriggerActionMapping::getActionDetails($tenant_id, $trigger_id);
                foreach ($actions as $action) {
                    TriggerActionService::callActionService($action, $log_id);
                }
            }
            return response()->json(['status'  => true, 'code' => 200, 'message' => 'Request Successfully Completed!']);
        } catch (Exception $e) {
            Logging::warning(json_encode($e));
            return $e->getMessage();
        }
    }


    /**
     * Handle Calendar Feedback Trigger, Manually Call This Method From Calendar Controller Update Function
     */
    public function calendarFeedback($params)
    {
        Logging::warning(json_encode($params));
        try {
            $tenant_id = $params['tenant_id'];
            $triggers = AutomatedWorkflowTrigger::where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', 'CalendarFeedback')
                ->where('automated_workflow_triggers.trigger_event', 'Received')
                ->where('automated_workflow_triggers.is_filter', false)
                ->select('automated_workflow_triggers.id AS trigger_id')
                ->pluck('trigger_id')->toArray();

            $trigger_ids = $triggers;
            if (empty($trigger_ids)) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Trigger not found for this request!']);
            }
            foreach ($trigger_ids as $trigger_id) {
                $log_id = AutomatedWorkflowLog::saveLog($tenant_id, $trigger_id, json_encode($params));
                $actions = AutomatedWorkflowTriggerActionMapping::getActionDetails($tenant_id, $trigger_id);
                foreach ($actions as $action) {
                    TriggerActionService::callActionService($action, $log_id);
                }
            }
            return response()->json(['status'  => true, 'code' => 200, 'message' => 'Request Successfully Completed!']);
        } catch (Exception $e) {
            Logging::warning(json_encode($e));
            return $e->getMessage();
        }
    }


    /**
     * Handle DocumentShared from Document Updated Webhook, Manually Call This Method From Document Webhook
     */
    public function documentShared($params)
    {
        Logging::warning(json_encode($params));
        try {
            $tenant_id = $params['tenant_id'];
            $triggers = AutomatedWorkflowTrigger::where('automated_workflow_triggers.tenant_id', $tenant_id)
                ->where('automated_workflow_triggers.primary_trigger', 'DocumentShared')
                ->where('automated_workflow_triggers.is_filter', false)
                ->select('automated_workflow_triggers.id AS trigger_id')
                ->pluck('trigger_id')->toArray();

            $trigger_ids = $triggers;
            if (empty($trigger_ids)) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Trigger not found for this request!']);
            }
            foreach ($trigger_ids as $trigger_id) {
                $log_id = AutomatedWorkflowLog::saveLog($tenant_id, $trigger_id, json_encode($params));
                $actions = AutomatedWorkflowTriggerActionMapping::getActionDetails($tenant_id, $trigger_id);
                foreach ($actions as $action) {
                    TriggerActionService::callActionService($action, $log_id);
                }
            }
            return response()->json(['status'  => true, 'code' => 200, 'message' => 'Request Successfully Completed!']);
        } catch (Exception $e) {
            Logging::warning(json_encode($e));
            return $e->getMessage();
        }
    }
}
