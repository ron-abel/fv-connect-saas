<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\SubscriptionPlans;
use App\Models\SubscriptionPlanMapping;

class SubscriptionPlanMappingController extends Controller
{

    /**
     * [GET] Subscription Plan Mapping List page for Super Admin
     */
    public function index()
    {
        try {
            $data['subscription_plans'] = SubscriptionPlans::where('plan_is_default', 1)->where('plan_is_active', 1)->get();
            $data['mappings'] = SubscriptionPlanMapping::join('subscription_plans', 'subscription_plan_mappings.subscription_plan_id', '=', 'subscription_plans.id')
                ->select('subscription_plan_mappings.*', 'subscription_plans.id as subscription_plan_id', 'subscription_plans.plan_name as plan_name')
                ->get();

            return $this->_loadContent('superadmin.pages.subscription_plan_mapping', $data);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [POST] Store Subscription Plan Mapping
     */
    public function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'subscription_plan_id'  => 'required',
                'project_count_from'  => 'required',
                'project_count_to' => 'required'
            ]);

            if ($validator->fails()) {
                $message = "Validation Failed! ";
                foreach ($validator->errors()->all() as $key => $value) {
                    $message .= $value . " ";
                }
                return redirect()->back()->with('error', $message);
            }

            $subscription_plan_mapping_id = $request->input('subscription_plan_mapping_id');
            $project_count_from = $request->input('project_count_from');
            $project_count_to = $request->input('project_count_to');

            if (!empty($subscription_plan_mapping_id)) {
                $subscriptionPlanMapping = SubscriptionPlanMapping::find($subscription_plan_mapping_id);
            } else {
                $subscriptionPlanMapping = new SubscriptionPlanMapping;
            }

            if ($project_count_from >= $project_count_to) {
                return redirect()->back()->with('error', '"Project Count From" Can not be greater than or equal "Project Count To"!');
            }

            $subscriptionPlanMapping->subscription_plan_id = $request->input('subscription_plan_id');
            $subscriptionPlanMapping->project_count_from = $project_count_from;
            $subscriptionPlanMapping->project_count_to = $project_count_to;
            $subscriptionPlanMapping->save();

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
            $mapping_id = $request->input('mapping_id');
            SubscriptionPlanMapping::where('id', $mapping_id)->delete();
            $response['success'] = true;
            $response['message'] = 'Subscription Plan Mapping Deleted successfully!';
            return response()->json($response);
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = 'There is a problem to deleted!';
            return response()->json($response);
        }
    }
}
