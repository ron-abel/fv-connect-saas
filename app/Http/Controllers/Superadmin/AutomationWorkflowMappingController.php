<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\AutomatedWorkflowInitialAction;
use App\Models\AutomatedWorkflowTriggerActionMappingRule;

class AutomationWorkflowMappingController extends Controller
{

    /**
     * [GET] Automated Workflow Mapping List page for Super Admin
     */
    public function index()
    {
        try {
            $data['actions'] = AutomatedWorkflowInitialAction::getStaticActionList();

            $data['mapping_rules'] = AutomatedWorkflowTriggerActionMappingRule::where('status', true)
                ->select('primary_trigger', 'trigger_event', DB::raw('group_concat(id) as ids'), DB::raw('group_concat(action_name) as action_name'), DB::raw('group_concat(action_short_code) as action_short_code'))
                ->groupBy('primary_trigger')
                ->groupBy('trigger_event')
                ->get();

            return $this->_loadContent('superadmin.pages.automation_workflow_mapping', $data);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [POST] Store Automated Workflow Trigger Action Mapping Rule
     */
    public function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'primary_trigger'  => 'required',
                'trigger_event'  => 'required',
                'action_name' => 'required'
            ]);

            if ($validator->fails()) {
                $message = "Validation Failed! ";
                foreach ($validator->errors()->all() as $key => $value) {
                    $message .= $value . " ";
                }
                return redirect()->back()->with('error', $message);
            }

            $mapping_ids = $request->input('mapping_ids');
            if (!empty($mapping_ids)) {
                $mapping_ids = explode(',', $mapping_ids);
                AutomatedWorkflowTriggerActionMappingRule::whereIn('id', $mapping_ids)->delete();
            }


            $primary_trigger = $request->input('primary_trigger');
            $trigger_events = explode("-", $request->input('trigger_event'));
            $action_names = $request->input('action_name');

            foreach ($trigger_events as $trigger_event) {
                foreach ($action_names as $action_name_code) {
                    $action_data = explode("-", $action_name_code);
                    $action_short_code =  $action_data[0];
                    $action_name =  $action_data[1];

                    // Check same trigger, event and action
                    $exist = AutomatedWorkflowTriggerActionMappingRule::where('primary_trigger', $primary_trigger)->where('trigger_event', $trigger_event)->where('action_short_code', $action_short_code)->exists();
                    if (!$exist) {
                        $automatedWorkflowTriggerActionMappingRule = new AutomatedWorkflowTriggerActionMappingRule();
                        $automatedWorkflowTriggerActionMappingRule->primary_trigger = $primary_trigger;
                        $automatedWorkflowTriggerActionMappingRule->trigger_event = $trigger_event;
                        $automatedWorkflowTriggerActionMappingRule->action_short_code = $action_short_code;
                        $automatedWorkflowTriggerActionMappingRule->action_name = $action_name;
                        $automatedWorkflowTriggerActionMappingRule->save();
                    }
                }
            }

            return redirect()->back()->with('success', "Setting Saved Successfully!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * [POST] Delete Automated Workflow Trigger Action Mapping Rule
     */
    public function delete(Request $request)
    {
        try {

            $rule_ids = explode(',', $request->rule_id);
            AutomatedWorkflowTriggerActionMappingRule::whereIn('id', $rule_ids)->delete();
            $response['success'] = true;
            $response['message'] = 'Trigger Event Action Deleted successfully!';
            return response()->json($response);
        } catch (\Exception $e) {

            $response['success'] = false;
            $response['message'] = 'There is a problem to deleted!';
            return response()->json($response);
        }
    }
}
