<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\FilevineService;
use App\Models\WebhookSettings;
use App\Models\WebhookLogs;
use App\Models\Tenant;
use Illuminate\Support\Facades\URL;
use Exception;
use Illuminate\Support\Facades\Log as Logging;

class WebhookController extends Controller
{
    public $cur_tenant_id;
    public function __construct()
    {
        Controller::setSubDomainName();
        $this->cur_tenant_id = session()->get('tenant_id');
    }

    /**
     * [GET] Webhook Page for Admin
     */
    public function index(Request $request)
    {
        try {
            // get the webhooks from db.
            $tenant_id = $this->cur_tenant_id;
            $webhooks = WebhookSettings::where(array('tenant_id' => $tenant_id, 'is_active' => 1))->orderBy('Id', 'DESC')->get();

            // get webhook logs for every webhook trigger.
            $webhook_logs = DB::select(DB::raw("select trigger_action_name , count(*) as count from webhook_logs group by trigger_action_name"));
            $webhook_logs_parse = [];
            if (count($webhook_logs) > 0) {
                foreach ($webhook_logs as $key => $value) {
                    $webhook_logs_parse[$value->trigger_action_name] = $value->count;
                }
            }

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
                $current_project_typeid = $mappings[0]->project_type_id;

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
            }

            return $this->_loadContent("admin.pages.webhook", ['webhooks' => $webhooks, 'webhook_logs_parse' => $webhook_logs_parse, 'project_type_phases' => $project_type_phases]);
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
     * [POST] Add, Update and Delete Webhook Settings
     */
    public function process_webhook(Request $request)
    {
        try {

            $tenant_id = $this->cur_tenant_id;

            $webhook_urls = [
                'PhaseChanged' => url('api/v1/webhook/phase_changed'),
                'ContactCreated' => url('api/v1/webhook/contact_created'),
                'ProjectCreated' => url('api/v1/webhook/project_created'),
                'CollectionItemCreated' => url('api/v1/webhook/collectionitem_created'),
                'TaskCreated' => url('api/v1/webhook/task_created'),
            ];

            if (isset($request->type) && !empty($request->type) && isset($request->fetch)) {
                $response = ['sucess' => false, 'data' => []];
                $type = $request->type;
                $if_phase_change = $request->phase;
                if ($type == 'PhaseChanged') {
                    $webhook = WebhookSettings::where([
                        'trigger_action_name' => $type,
                        'item_change_type' => $if_phase_change,
                        'tenant_id' => $tenant_id,
                    ])
                        ->get();
                    $webhook = json_decode(json_encode($webhook), true);
                    if (count($webhook)) {
                        $response['success'] = true;
                        $response['data'] = [
                            'destination' => $webhook[0]['delivery_hook_url'],
                            'phase_change' => $webhook[0]['phase_change_event']
                        ];
                    }
                } else {
                    $webhook = WebhookSettings::where([
                        'trigger_action_name' => $type,
                        'tenant_id' => $tenant_id,
                    ])
                        ->get();
                    $webhook = json_decode(json_encode($webhook), true);
                    if (count($webhook)) {
                        $response['success'] = true;
                        $response['data'] = [
                            'destination' => $webhook[0]['delivery_hook_url'],
                            'phase_change' => ''
                        ];
                    }
                }
                echo json_encode($response);
                exit();
            } else if (isset($request->trigger_action) && !empty($request->trigger_action)) {
                $response = ['success' => false, 'message' => ''];
                $type = $request->trigger_action;
                $dest = $request->delivery_hook_url;
                $url = $webhook_urls[$type];
                // check if subscription already not created
                $is_exist = WebhookSettings::where([
                    'trigger_action_name' => $type,
                    'tenant_id' => $tenant_id,
                ])->get();

                $is_exist = json_decode(json_encode($is_exist), true);
                if ($type == 'PhaseChanged') {
                    $if_phase_change = $request->item_change_type;
                    $phase_change = $request->phase_change_event;
                    $fv_phase_id = $request->fv_phase_id;
                    $is_new = $request->is_new;
                    $webhook_id = $request->id;
                    $is_created_fv_subscription = 0;

                    if (!empty($dest) && !empty($phase_change)) {

                        $is_exist_phase_changed = WebhookSettings::where('id', $webhook_id)->get();

                        if ($is_new == 1 && count($is_exist_phase_changed) <= 0) {

                            $eventIds = "Project.PhaseChanged";
                            $fv_response = $this->createWebhookSubscriptionInFilevine($eventIds, $url, $is_exist, $type);
                            if ($fv_response) {
                                $filevine = $fv_response['filevine'];
                                $is_created_fv_subscription = $fv_response['is_created_fv_subscription'];
                            }

                            $values = array(
                                'tenant_id' => $tenant_id,
                                'trigger_action_name' => $type,
                                'delivery_hook_url' => $dest,
                                'filevine_hook_url' => $url,
                                'item_change_type' => $if_phase_change,
                                'phase_change_event' => $phase_change,
                                'fv_phase_id' => $fv_phase_id
                            );
                            $new_web_hook = WebhookSettings::create($values);

                            $response['success'] = true;
                            $response['message'] = 'Setting saved successfully!';
                            $response['data'] = $filevine;
                            $response['new_data'] = $new_web_hook;
                        } else {
                            $values = array(
                                'delivery_hook_url' => $dest,
                                'item_change_type' => $if_phase_change,
                                'phase_change_event' => $phase_change,
                                'fv_phase_id' => $fv_phase_id,
                                'is_active' => 1
                            );
                            WebhookSettings::where('id', $webhook_id)->update($values);

                            $response['success'] = true;
                            $response['message'] = 'Setting saved successfully!';
                        }
                    } else {
                        $response['error'] = true;
                        $response['message'] = 'All fields are required';
                    }
                    $response['is_created_fv_subscription'] = $is_created_fv_subscription;
                } else if ($type == 'CollectionItemCreated') {
                    $if_collection = $request->item_change_type;
                    $collection_changed = $request->collection_changed;
                    $is_new = $request->is_new;
                    $webhook_id = $request->id;
                    $is_created_fv_subscription = 0;

                    if (!empty($dest) && !empty($collection_changed)) {

                        $is_exist_collection_changed = WebhookSettings::where('id', $webhook_id)->get();

                        if ($is_new == 1 && count($is_exist_collection_changed) <= 0) {

                            $eventIds = "CollectionItem.Created";
                            $fv_response = $this->createWebhookSubscriptionInFilevine($eventIds, $url, $is_exist, $type);
                            if ($fv_response) {
                                $filevine = $fv_response['filevine'];
                                $is_created_fv_subscription = $fv_response['is_created_fv_subscription'];
                            }

                            $values = array(
                                'tenant_id' => $tenant_id,
                                'trigger_action_name' => $type,
                                'delivery_hook_url' => $dest,
                                'filevine_hook_url' => $url,
                                'item_change_type' => $if_collection,
                                'collection_changed' => $collection_changed
                            );
                            $new_web_hook = WebhookSettings::create($values);

                            $response['success'] = true;
                            $response['message'] = 'Setting saved successfully!';
                            $response['data'] = $filevine;
                            $response['new_data'] = $new_web_hook;
                        } else {
                            $values = array(
                                'delivery_hook_url' => $dest,
                                'item_change_type' => $if_collection,
                                'collection_changed' => $collection_changed,
                                'is_active' => 1
                            );
                            WebhookSettings::where('id', $webhook_id)->update($values);

                            $response['success'] = true;
                            $response['message'] = 'Setting saved successfully!';
                        }
                    } else {
                        $response['error'] = true;
                        $response['message'] = 'All fields are required';
                    }
                    $response['is_created_fv_subscription'] = $is_created_fv_subscription;
                } else if ($type == 'TaskCreated') {
                    $if_task = $request->item_change_type;
                    $task_changed = $request->task_changed;
                    $is_new = $request->is_new;
                    $webhook_id = $request->id;
                    $is_created_fv_subscription = 0;

                    if (!empty($dest) && !empty($task_changed)) {

                        $is_exist_task_changed = WebhookSettings::where('id', $webhook_id)->get();

                        if ($is_new == 1 && count($is_exist_task_changed) <= 0) {

                            $eventIds = "Task.Created";
                            $fv_response = $this->createWebhookSubscriptionInFilevine($eventIds, $url, $is_exist, $type);
                            if($fv_response){
                                $filevine = $fv_response['filevine'];
                                $is_created_fv_subscription = $fv_response['is_created_fv_subscription'];
                            }

                            $values = array(
                                'tenant_id' => $tenant_id,
                                'trigger_action_name' => $type,
                                'delivery_hook_url' => $dest,
                                'filevine_hook_url' => $url,
                                'item_change_type' => $if_task,
                                'task_changed' => $task_changed
                            );
                            $new_web_hook = WebhookSettings::create($values);

                            $response['success'] = true;
                            $response['message'] = 'Setting saved successfully!';
                            $response['data'] = $filevine;
                            $response['new_data'] = $new_web_hook;
                        } else {
                            $values = array(
                                'delivery_hook_url' => $dest,
                                'item_change_type' => $if_task,
                                'task_changed' => $task_changed,
                                'is_active' => 1
                            );
                            WebhookSettings::where('id', $webhook_id)->update($values);

                            $response['success'] = true;
                            $response['message'] = 'Setting saved successfully!';
                        }
                    } else {
                        $response['error'] = true;
                        $response['message'] = 'All fields are required';
                    }
                    $response['is_created_fv_subscription'] = $is_created_fv_subscription;
                } else {
                    $is_created_fv_subscription = 0;
                    if (count($is_exist) <= 0) {

                        if ($type == 'ContactCreated') {
                            $eventIds = "Contact.Created";
                        } else if ($type == 'ProjectCreated') {
                            $eventIds = "Project.Created";
                        }

                        $fv_response = $this->createWebhookSubscriptionInFilevine($eventIds, $url, $is_exist, $type);
                        if($fv_response){
                            $filevine = $fv_response['filevine'];
                            $is_created_fv_subscription = $fv_response['is_created_fv_subscription'];
                        }

                        $values = array(
                            'tenant_id' => $tenant_id,
                            'trigger_action_name' => $type,
                            'delivery_hook_url' => $dest,
                            'filevine_hook_url' => $url
                        );
                        $new_value = WebhookSettings::create($values);

                        $response['success'] = true;
                        $response['message'] = 'Setting saved successfully!';
                        $response['data'] = $filevine;
                        $response['new_data'] = $new_value;
                    } else {
                        // needs to update
                        $values = array('delivery_hook_url' => $dest, 'is_active' => 1);
                        $update_value = WebhookSettings::where('trigger_action_name', $type)->update($values);

                        $response['success'] = true;
                        $response['message'] = 'Setting saved successfully!';
                        $response['new_data'] = $update_value;
                    }
                    $response['is_created_fv_subscription'] = $is_created_fv_subscription;
                }


                echo json_encode($response);
                exit();
            } elseif ($request->delete_action) {
                $response = $this->deleteWebhook($request);
                echo json_encode($response);
                exit();
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * [GET] Delete Webhook
    */

    public function deleteWebhook($request)
    {
        try {
            $response = ['success' => false, 'message' => ''];

            $webhook_id = $request->id;

            if ($request->delete_action == 'delete_action') {
                $values = array('is_active' => 0);
                WebhookSettings::where('id', $webhook_id)->update($values);

                $response['success'] = true;
                $response['message'] = 'Setting saved successfully!';
            }

            return $response;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * [GET] Create Filevine Subscription
    */

    public function createWebhookSubscriptionInFilevine($eventIds, $webhook_url, $is_exist, $type)
    {
        try {
            $is_created_fv_subscription = 0;
            $tenant_id = $this->cur_tenant_id;
            $Tenant = Tenant::find($tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $obj = new FilevineService($apiurl, "");

            if($eventIds == null){
                $eventIds = "Project.PhaseChanged";
            }

            $get_all_subscriptions = $obj->getSubscriptionsList();
            $get_all_subscriptions = json_decode($get_all_subscriptions);

            $filevine_exist = "";
            foreach ($get_all_subscriptions as $single_subscription) {
                if ($single_subscription->eventIds == [$eventIds] && $single_subscription->endpoint == $webhook_url) {
                    $filevine_exist = "AlreadyExists";
                }
            }

            if ($filevine_exist == 'AlreadyExists') {
                $filevine = 'AlreadyExists';
                if (count($is_exist) == 0) {
                    $is_created_fv_subscription = 1;
                }
            } else {
                $filevine = $obj->createSubscription($type, $webhook_url, $eventIds);
                $filevine = json_decode($filevine);
                $is_created_fv_subscription = 1;
            }

            $response = array('filevine' => $filevine, 'is_created_fv_subscription' => $is_created_fv_subscription);

            return $response;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * [GET] Fetch Webhook Page data for Admin
     */
    public function fetchData($subdomain, $trigger_action)
    {
        try {
            // get the webhooks from db.
            $tenant_id = $this->cur_tenant_id;
            $trigger_action_name = $trigger_action;
            $webhooks = WebhookSettings::where(array('tenant_id' => $tenant_id, 'is_active' => 1))->orderBy('Id', 'DESC')->get();

            // get webhook logs for every webhook trigger.

            $webhook_logs = DB::select(DB::raw("select trigger_action_name , count(*) as count from webhook_logs group by trigger_action_name"));
            $webhook_logs_parse = [];
            if (count($webhook_logs) > 0) {
                foreach ($webhook_logs as $key => $value) {
                    $webhook_logs_parse[$value->trigger_action_name] = $value->count;
                }
            }

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
                $current_project_typeid = $mappings[0]->project_type_id;

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
            }


            $html = view("admin.pages.webhook_ajax", ['webhooks' => $webhooks, 'webhook_logs_parse' => $webhook_logs_parse, 'subdomain' => $subdomain, 'trigger_action_name' => $trigger_action_name, 'project_type_phases'=>$project_type_phases])->render();
            return \response()->json(['status' => 200, 'success' => true, 'html' => $html]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
