<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log as Logging;
use Illuminate\Support\Facades\Validator;

use App\Models\LegalteamConfig;
use App\Models\SubscriptionCustomer;
use App\Models\Tenant;
use App\Models\TenantLive;
use App\Models\User;
use App\Models\SubscriptionPlans;

use App\Services\SendGridServices;
use App\Services\CampaignMonitorService;
use App\Services\ExportService;
use App\Services\SlackServices;
use App\Services\SubscriptionService;
use App\Services\FilevineService;

use Auth;
use Exception;
use Response;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class TenantsController extends Controller
{


    private $sendGridServices;
    private $subscriptionServices;
    private $slackServices;
    private $cmServices;


    public function __construct(SendGridServices $sendGridServices, SubscriptionService $subscriptionServices, SlackServices $slackServices, CampaignMonitorService $cmServices)
    {
        $this->sendGridServices = $sendGridServices;
        $this->subscriptionServices = $subscriptionServices;
        $this->slackServices = $slackServices;
        $this->cmServices = $cmServices;
        Controller::setSubDomainName();
    }

    /**
     * [GET] Tenants Usage dashboard page for Super Admin
     */
    public function usageDashboard()
    {
        try {
            $all_plans = [];
            $max_price_plan = "";
            $max_price = 0;
            // $plans = [];
            // get all plans
            $plans = $this->subscriptionServices->retrieveTotalPlans();
            if (is_array($plans)) {
                foreach ($plans as $plan) {
                    $all_plans[$plan->id] = ($plan->amount / 100) . '/' . $plan->interval;
                    if ($max_price < $plan->amount) {
                        $max_price = $plan->amount;
                        $max_price_plan = $plan->id;
                    }
                }
            }


            $all_tenants = Tenant::with('customer')->where('tenant_name', '!=', config('app.superadmin'))->get();
            return $this->_loadContent('superadmin.pages.tenants-usage-dashboard', [
                'all_tenants' => $all_tenants,
                'all_plans' => $all_plans,
                'plans' => $plans,
                'max_price_plan' => $max_price_plan
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [GET] Tenants List page for Super Admin
     */
    public function index()
    {
        try {
            if (config('app.env') == 'local') {
                $http = "http://";
            } else {
                $http = "https://";
            }

            $domainName = config('app.domain');

            $all_plans = [];
            $max_price_plan = "";
            $max_price = 0;
            // get all plans
            $plans = $this->subscriptionServices->retrievePlans();
            if (is_array($plans)) {
                foreach ($plans as $plan) {
                    $all_plans[$plan->product->id] = $plan->product->name;
                    if ($max_price < $plan->amount) {
                        $max_price = $plan->amount;
                        $max_price_plan = $plan->id;
                    }
                }
            }

            // Update subscriptions table ends_at
            $active_customer_list = [];
            $subscriptions = $this->subscriptionServices->getAllActiveSubscription();
            if (count($subscriptions) > 0)
                foreach ($subscriptions as $subscription) {
                    $active_customer_list[] = $subscription->customer;
                    $subscription_details = DB::table('subscriptions')->where('stripe_id', $subscription->id)->first();
                    if ($subscription_details != null) {
                        $timestamp = $subscription->current_period_end ?? '';
                        $ends_at = Carbon::createFromTimeStamp($timestamp)->format('Y-m-d');
                        $ends_at = Carbon::createFromFormat('Y-m-d', $ends_at)->endOfDay()->toDateTimeString();
                        $sub_data['ends_at'] = $ends_at;
                        if (!empty($subscription->cancel_at)) {
                            $needs_cancel_at = Carbon::createFromTimeStamp($subscription->cancel_at)->format('Y-m-d');
                            $needs_cancel_at = Carbon::createFromFormat('Y-m-d', $needs_cancel_at)->endOfDay()->toDateTimeString();
                            $sub_data['needs_cancelled_at'] = $needs_cancel_at;
                        }

                        DB::table('subscriptions')->where('stripe_id', $subscription->id)->update($sub_data);
                    }
                }

            // Update customer_subscriptions table with cancel
            $canceled_subscriptions = $this->subscriptionServices->getAllCancelSubscription();
            if (count($canceled_subscriptions) > 0)
                foreach ($canceled_subscriptions as $canceled_subscription) {
                    if (!in_array($canceled_subscription->customer, $active_customer_list)) {
                        $subscriptionCustomer = SubscriptionCustomer::where('stripe_id', '=', $canceled_subscription->customer)->first();
                        if ($subscriptionCustomer != null) {

                            SubscriptionCustomer::where('stripe_id', '=', $canceled_subscription->customer)->update([
                                'is_active' => 0,
                                'is_canceled' => 1,
                                'is_expired' => 0
                            ]);
                        }
                    }
                }

            // Compare subscription table data with real strip data
            $all_tenants = Tenant::with('owner', 'customer')->where('tenant_name', '!=', config('app.superadmin'))->get();
            foreach ($all_tenants as $single_tenant) {
                if ($single_tenant->customer) {
                    $this->updateSubscription($single_tenant->customer->tenant_id);

                    if ($single_tenant->customer && $single_tenant->customer->subscribed('default')) {

                        if (isset($single_tenant->customer->subscription('default')->items[0]->stripe_product)) {

                            $stripe_product = $single_tenant->customer->subscription('default')->items[0]->stripe_product;
                            if (array_key_exists($stripe_product, $all_plans)) {
                                if (!$single_tenant->customer->is_active && count($all_plans)) {
                                    $single_tenant->customer->update([
                                        'is_active' => 1,
                                        'is_canceled' => 0,
                                        'is_expired' => 0
                                    ]);
                                }
                            } else {
                                if (!$single_tenant->customer->is_canceled && count($all_plans) && !SubscriptionCustomer::checkActiveSubscription($single_tenant->customer->id)) {
                                    $single_tenant->customer->update([
                                        'is_active' => 0,
                                        'is_expired' => 1
                                    ]);
                                }
                            }
                        } else {
                            if (!$single_tenant->customer->is_canceled && count($all_plans) && !SubscriptionCustomer::checkActiveSubscription($single_tenant->customer->id)) {
                                $single_tenant->customer->update([
                                    'is_active' => 0,
                                    'is_expired' => 1
                                ]);
                            }
                        }
                    } else {
                        if (in_array($single_tenant->customer->stripe_id, $active_customer_list)) {
                            $single_tenant->customer->update([
                                'is_active' => 1,
                                'is_canceled' => 0,
                                'is_expired' => 0
                            ]);
                        } else {
                            if (!$single_tenant->customer->is_canceled && count($all_plans) && !SubscriptionCustomer::checkActiveSubscription($single_tenant->customer->id)) {
                                $single_tenant->customer->update([
                                    'is_active' => 0,
                                    'is_expired' => 1
                                ]);
                            }
                        }
                    }
                }
            }

            $all_tenants = Tenant::with('owner', 'customer')->where('tenant_name', '!=', config('app.superadmin'))->get();


            foreach ($all_tenants as $key => $single_tenant) {
                $tenant_IP_config = DB::table('config')->select('ip_verification_enable')->where('tenant_id', '=', $single_tenant['id'])->first();
                if (is_null($tenant_IP_config)) {
                    $all_tenants[$key]['ip_verification_enable'] = 0;
                } else {
                    $all_tenants[$key]['ip_verification_enable'] = $tenant_IP_config->ip_verification_enable;
                }

                $all_tenants[$key]['plan_price'] = "";
                if ($single_tenant->customer && isset($single_tenant->customer->subscription('default')->items[0]->stripe_product)) {
                    $sub_plan = SubscriptionPlans::where('stripe_product_id', $single_tenant->customer->subscription('default')->items[0]->stripe_product)->first();
                    $all_tenants[$key]['plan_price'] = $sub_plan != null ? "$" . $sub_plan->plan_price . "/" . $sub_plan->plan_interval : '';
                }

                if ($single_tenant->customer && ($single_tenant->customer->is_expired || $single_tenant->customer->is_canceled) && isset($single_tenant->customer->subscription('default')->items[0]->stripe_product)) {
                    $all_tenants[$key]['plan_name'] = $sub_plan != null ? $sub_plan->plan_name : '';
                    $all_tenants[$key]['plan_start_date'] = $sub_plan != null ? date('d/m/Y', strtotime($sub_plan->created_at)) : '';
                } else {
                    $all_tenants[$key]['plan_name'] = "";
                    $all_tenants[$key]['plan_start_date'] = "";
                }

                if ($single_tenant->customer && isset($single_tenant->customer->subscription('default')->needs_cancelled_at) && !empty($single_tenant->customer->subscription('default')->needs_cancelled_at)) {
                    $all_tenants[$key]['plan_cancel_date'] = date('d/m/Y', strtotime($single_tenant->customer->subscription('default')->needs_cancelled_at));
                } else {
                    $all_tenants[$key]['plan_cancel_date'] = "";
                }
            }

            return $this->_loadContent('superadmin.pages.tenants', [
                'all_tenants' => $all_tenants,
                'all_plans' => $all_plans,
                'plans' => $plans,
                'max_price_plan' => $max_price_plan,
                'domainName'  => $domainName,
                'http'        => $http,
            ]);
        } catch (Exception $e) {
            $exception_json = json_encode($e);
            Logging::warning($exception_json);
            return $e->getMessage();
        }
    }


    /**
     * Update Subscription status based on trial and end date
     */
    public function updateSubscription($tenant_id)
    {
        try {
            $subscribedCustomer = SubscriptionCustomer::where('tenant_id', $tenant_id)->first();
            if (isset($subscribedCustomer->id)) {
                $subscriptions = DB::table('subscriptions')->where('subscription_customer_id', $subscribedCustomer->id)->whereNotIn('stripe_status', ['canceled', 'ended'])->get();
                foreach ($subscriptions as $subscription) {
                    $trial_ends_at = $subscription->trial_ends_at;
                    $ends_at = Carbon::parse($subscription->ends_at)->format('Y-m-d');
                    $ends_at = Carbon::createFromFormat('Y-m-d', $ends_at)->endOfDay()->toDateTimeString();

                    if (!empty($trial_ends_at) && (time() > strtotime($trial_ends_at)) && (time() <= strtotime($ends_at))) {
                        DB::table('subscriptions')->where('id', $subscription->id)->update([
                            'updated_at' => Carbon::now(),
                            'stripe_status' => 'active'
                        ]);
                    } else if (!empty($ends_at) && (time() > strtotime($ends_at))) {
                        DB::table('subscriptions')->where('id', $subscription->id)->update([
                            'updated_at' => Carbon::now(),
                            'stripe_status' => 'ended'
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Logging::warning($e->getMessage());
        }
    }

    /**
     * [GET] Create Tenants page for Super Admin
     */
    public function add_tenant()
    {
        try {
            return $this->_loadContent('superadmin.pages.add_tenant');
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * toggle tenant admin ip_verifaction_enable
     */
    public function toggle_ip_verification_enable(Request $request)
    {
        try {
            $res = 1;
            $tenant_IP_config = DB::table('config')->select('ip_verification_enable')->where('tenant_id', '=', $request->post('tenant_id'))->first();
            if (is_null($tenant_IP_config)) {
                $res = DB::table('config')->insert(['tenant_id' => $request->post('tenant_id'), 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'ip_verification_enable' => $request->post('value') == 1 ? 0 : 1]);
            } else {
                $res = DB::table('config')
                    ->where('tenant_id', $request->post('tenant_id'))
                    ->update(['ip_verification_enable' => $request->post('value') == 1 ? 0 : 1]);
            }
            if ($res == 1) {
                return response()->json([
                    'success'        => true,
                    'value' => $request->post('value') == 1 ? 0 : 1
                ], 200);
            } else {
                return response()->json([
                    'success'        => false,
                ], 400);
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * [POST] Resend Verification
     */
    public function reverify_tenant($tenant_id, Request $request)
    {
        //tenant_name, tenant_owner_email, tenant_owner_name
        try {
            $tenants = DB::table('tenants')
                ->select('tenants.tenant_name', 'users.full_name', 'users.email')
                ->join('users', 'users.tenant_id', '=', 'tenants.id')
                ->where('tenants.id', $tenant_id)
                ->first();
            if ($tenants) {
                $token = $tenants->tenant_name . \Str::random(32);
                $tenant_owner =  User::where('email', $tenants->email)->update([
                    'remember_token' => $token,
                ]);


                // Send registration confirm email
                $this->sendGridServices->resendVerification($tenants, $token);

                // Slack webhook
                $this->slackServices->sendMessage($tenant_owner);

                return response()->json([
                    'success'        => true,
                    'message'  => 'Reverifaction Sent'
                ], 200);
            } else {
                return response()->json([
                    'success'        => true,
                    'message'  => 'Tenant not found'
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'success'        => false,
                'message'  => $e->getMessage()
            ], 200);
        }
    }

    /**
     * [POST] Create Tenants page for Super Admin
     */
    public function add_tenant_post(Request $request)
    {

        $request->validate([
            'fv_tenant_base_url' => 'required',
            'tenant_name' => 'required|regex:/^[a-z]+$/|string|min:5|unique:tenants,tenant_name',
            'tenant_description' => 'required|string',
            'test_tfa_number' => 'required|string|unique:tenant_lives,test_tfa_number',
            'owner_email' => 'nullable|email'
        ]);

        try {
            $current_date = date('Y-m-d H:i:s');

            // check fv_api_base_url
            $fv_tenant_base_url = $request->input('fv_tenant_base_url');
            $fv_api_base_url = $this->_getFVAPIBaseUrl($fv_tenant_base_url);

            $values = array(
                'tenant_name' => $request->input('tenant_name'),
                'fv_tenant_base_url' => $fv_tenant_base_url,
                'fv_api_base_url' => $fv_api_base_url,
                'tenant_description' => $request->input('tenant_description'),
                'created_at' => $current_date,
                'is_verified' => 1,
            );

            $token = $request['tenant_name'] . \Str::random(32);

            $tenant_admin_user_email = $request->owner_email;
            if (empty($tenant_admin_user_email)) {
                $tenant_admin_user_email = $request['tenant_name'] . '.' . 'admin@vinetegrate.com';
            }

            $owner_name = $request->owner_name;
            if (empty($owner_name)) {
                $owner_name = $tenant_admin_user_email;
            }


            $exitsuser = User::where('email', $tenant_admin_user_email)->first();
            if (isset($exitsuser)) {
                $tenant_obj = Tenant::find($exitsuser->tenant_id);
                $tenant_obj->update($values);
                if (isset($owner_name) && $exitsuser->full_name != $owner_name) {
                    $tenant_owner = User::where('email', $tenant_admin_user_email)->get();
                    User::where('email', $tenant_admin_user_email)->update(['full_name' => $owner_name]);
                } else {
                    $tenant_owner = $exitsuser;
                }
                TenantLive::where('tenant_id', $exitsuser->tenant_id)->update(['status' => 'setup', 'test_tfa_number' => $request->input('test_tfa_number')]);
            } else {
                $tenant_obj = Tenant::create($values);
                //create Manage User
                $tenant_owner =  User::create([
                    'user_role_id' => User::TENANT_OWNER,
                    'full_name' => $owner_name,
                    'email' => $tenant_admin_user_email,
                    'tenant_id' => $tenant_obj->id,
                    'password' => Hash::make($request['tenant_name'] . 'password'),
                    'remember_token' => $token,
                ]);

                TenantLive::create([
                    'tenant_id' => $tenant_obj->id,
                    'status' => 'setup',
                    'test_tfa_number' => $request->input('test_tfa_number')
                ]);
            }

            // Send registration confirm email
            $this->sendGridServices->sendConfirmRegistration($tenant_owner, $tenant_obj);


            // Slack webhook
            $this->slackServices->sendMessage($tenant_owner);
            return redirect()->route('tenants');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * [GET] Edit Tenants page for Super Admin
     */
    public function edit_tenant($tenant_id)
    {

        try {
            $tenant_details =  DB::table('tenants')->where('id', $tenant_id)->first();

            if ($tenant_details) {
                return $this->_loadContent('superadmin.pages.edit_tenant', compact('tenant_details'));
            } else {
                return abort(404);
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [POST] Edit Tenants page for Super Admin
     */
    public function edit_tenant_post($tenant_id, Request $request)
    {
        $request->validate([
            'tenant_name' => 'required|regex:/^[a-z]+$/|string|min:5|unique:tenants,tenant_name,' . $tenant_id,
            'tenant_description' => 'required|string',
        ]);

        try {
            $current_date = date('Y-m-d H:i:s');

            $tenant_details =  DB::table('tenants')->where('id', $tenant_id)->first();

            if ($tenant_details) {
                $is_active = '0';
                if ($request->has('is_active')) {
                    $is_active = '1';
                }
                $values = array('tenant_name' => $request->input('tenant_name'), 'tenant_description' => $request->input('tenant_description'), 'updated_at' => $current_date, 'is_active' => $is_active);

                $tenant_details_update = DB::table('tenants')->where('id', $tenant_id)->update($values);

                if ($tenant_details_update) {
                    return redirect()->route('edit_tenant', ['tenant_id' => $tenant_id])->with('success', 'Tenant Successfully Updated');
                } else {
                    return redirect()->route('edit_tenant', ['tenant_id' => $tenant_id])->with('error', 'No Records Updated');
                }
            } else {
                return abort(404);
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [POST] Delete Tenants for Super Admin
     */
    public function delete_tenant($tenant_id)
    {
        try {
            $db_name = env('DB_DATABASE');

            $tables = DB::select('SHOW TABLES');
            foreach ($tables as $table) {
                $tables_in  = "Tables_in_" . $db_name;
                $table_name = $table->$tables_in;
                $isColExist = Schema::connection(env('DB_CONNECTION'))->hasColumn($table_name, 'tenant_id');
                if ($isColExist) {
                    $deleted_tenant =  DB::table($table_name)->where('tenant_id', $tenant_id)->delete();
                }
            }
            $tenant_details =  DB::table('tenants')->where('id', $tenant_id)->first();

            if ($tenant_details) {

                $tenant_delete = Tenant::find($tenant_id)->delete();

                return Response::json(array('success' => true, 'tenants_url' => route('tenants')));
            } else {
                return abort(404);
            }
        } catch (Exception $e) {
            $e->getMessage();
        }
    }


    /**
     * [GET] View for tenant
     */
    public function view_tenant($tenant_id)
    {
        try {
            $tenant_details = Tenant::where('id', $tenant_id)->first();
            return $this->_loadContent('superadmin.pages.tenant_users', compact('tenant_details'));
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }



    /**
     * [POST] Reset User password
     */
    public function resetUser(Request $request, $id)
    {
        try {
            $user = User::findorFail($id);
            $request->validate([
                'email' => 'required',
            ]);
            if ($request['new-password'] != null && $request['new-password'] != '') {
                $request->validate([
                    'new-password' => "min:6",
                    'confirm-password' => "same:new-password"
                ]);
                $user->update([
                    'password' => Hash::make($request['new-password'])
                ]);
            }
            $user->update([
                'email' => $request['email']
            ]);
            return redirect()->back()->with('success', 'User Updated Successfully');
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * [POST] Confirm Registration
     */
    public function confirmVerification($subdomain, $token)
    {
        try {

            $tenant_owner = User::where('remember_token', $token)->first();
            $tenant = null;
            if (isset($tenant_owner->tenant_id)) {
                $tenant = Tenant::find($tenant_owner->tenant_id);
            }

            if ($tenant_owner && $tenant) {

                // Call CM Verfied Tenant
                $this->cmServices->verifiedTenant([
                    'tenant_id' => $tenant->id,
                    'tenant_name' => $tenant->tenant_name,
                    'owner_email' => $tenant_owner->email,
                    'owner_first_name' => $tenant_owner->full_name,
                    'owner_created_at' => $tenant_owner->created_at
                ]);

                $tenant_owner->update([
                    'email_verified_at' => now(),
                    'remember_token' => null,
                ]);

                $tenant_owner->tenant->update([
                    'is_verified' => 1,
                ]);

                //send successful registration mail
                $this->sendGridServices->sendReigstrationSuccessMail($tenant_owner);
            }

            return redirect()->route('admin.login', $subdomain)->with('success', 'Your registration is confirmed. You may now log in.');
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [GET] Export Tenants CSV
     */
    public function exportTenantsCsv(Request $request)
    {
        $service = new ExportService();
        return $service->exportTenantsCsv($request);
    }
    /**
     * [GET] Show Registration form for Tenants
     */
    public function selfRegistration()
    {
        return $this->_loadContent('superadmin.pages.register_tenant');
    }

    /**
     * [GET] Show Registration form for Tenants
     */
    public function postSelfRegistration(Request $request)
    {

        $request->validate([
            'fv_tenant_base_url' => 'required',
            'tenant_name' => 'required|regex:/^[a-z]+$/|string|min:5|unique:tenants,tenant_name',
            'admin-name' => 'required',
            'email' => "required|email|unique:users,email",
            'password' => 'required | min:6',
            'confirm_password' => 'required|same:password',
            'test_tfa_number' => 'required|string|unique:tenant_lives,test_tfa_number',
        ]);

        try {
            // check fv_api_base_url
            $fv_tenant_base_url = $request->input('fv_tenant_base_url');
            $fv_api_base_url = $this->_getFVAPIBaseUrl($fv_tenant_base_url);

            $tenant_obj = Tenant::create([
                'tenant_name' => $request['tenant_name'],
                'fv_tenant_base_url' => $fv_tenant_base_url,
                'fv_api_base_url' => $fv_api_base_url,
                'tenant_description' => $request['firm-name'],
            ]);

            $token = $request['tenant_name'] . \Str::random(32);

            //create Manage User
            $tenant_owner =  User::create([
                'user_role_id' => User::TENANT_OWNER,
                'full_name' => $request['admin-name'],
                'email' => $request['email'],
                'tenant_id' => $tenant_obj->id,
                'password' => Hash::make($request['password']),
                'remember_token' => $token,
            ]);

            // Create entry on tenant live table
            TenantLive::create([
                'tenant_id' => $tenant_obj->id,
                'status' => 'setup',
                'test_tfa_number' => $request->input('test_tfa_number')
            ]);

            // Call CM Registered
            $this->cmServices->registerTenant([
                'tenant_id' => $tenant_obj->id,
                'tenant_name' => $request['tenant_name'],
                'owner_email' => $request['email'],
                'owner_first_name' => $request['admin-name'],
                'owner_created_at' => date("Y-m-d H:i:s")
            ]);

            // Send registration confirm email
            $this->sendGridServices->sendConfirmRegistration($tenant_owner, $tenant_obj, 0);
            $infoMessage =  "Registration successfull! Please check your email for confirmation link.";

            // SLack webhook
            $this->slackServices->sendMessage($tenant_owner);

            // start create contact on FV
            $names = explode(" ", $tenant_owner->full_name);
            $contact_data = [
                "firstName" => $names[0],
                "lastName" => $names[1],
                "fullName" => $tenant_owner->full_name,
                "personTypes" => ["Firm"],
                "emails" => [
                    [
                        "label" => 'Tenant Email',
                        "address" => $tenant_owner->email
                    ]
                ],
                "phones" => [
                    [
                        "number" => $request->input('test_tfa_number'),
                        'rawNumber' => $request->input('test_tfa_number'),
                    ]
                ]
            ];
            $apiurl = config('services.fv.default_api_base_url');
            $request_data = [
                'fv_api_key' => config('services.fv.default_api_key'),
                'fv_key_secret' => config('services.fv.default_api_key_secret')
            ];
            $request_data = request()->merge([
                'fv_api_key' => config('services.fv.default_api_key'),
                'fv_key_secret' => config('services.fv.default_api_key_secret')
            ]);
            $default_api_project_type_id = config('services.fv.default_api_project_type_id');
            $filevine_api = new FilevineService($apiurl, $request_data);

            // send request to FV api for contact creation
            $contact = json_decode($filevine_api->createContact($contact_data));

            if (!empty($contact)) {
                $client_id = $contact->personId->native;
                $project_params = [
                    'projectName' => $request['tenant_name'],
                    'projectTypeId' => [
                        'native' => $default_api_project_type_id
                    ],
                    'clientId' => [
                        'native' => $client_id
                    ]
                ];
                $project = json_decode($filevine_api->createProject($project_params));
                if (!empty($project)) {
                    $this->slackServices->sendProjectCreationMessage($project);
                }
            }

            return $this->_loadContent('admin.pages.info', compact('infoMessage'));
        } catch (Exception $e) {
            $exception_json = json_encode($e);
            Logging::warning($exception_json);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * [POST] Edit Tenants Active/Inactive for Super Admin
     */
    public function editTenantActiveStatus($tenant_id, Request $request)
    {
        try {
            $tenant_details =  DB::table('tenants')->where('id', $tenant_id)->first();

            if ($tenant_details) {
                $values = array('is_active' => $request->input('status'));

                $tenant_details_update = DB::table('tenants')->where('id', $tenant_id)->update($values);

                if ($tenant_details_update) {
                    return response()->json([
                        'success'        => true,
                        'message'  => 'Tenant status changed successfully'
                    ], 200);
                } else {
                    return response()->json([
                        'success'        => false,
                        'message'  => 'Unable to change status at the moment'
                    ], 200);
                }
            } else {
                return response()->json([
                    'success'        => false,
                    'message'  => 'Unable to change status at the moment'
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'success'        => false,
                'message'  => $e->getMessage()
            ], 200);
        }
    }

    /**
     * [POST] Edit Tenants Subscription Plan for Super Admin
     */
    public function upgradeTenantPlan($tenant_id, Request $request)
    {
        try {
            $tenant_details =  Tenant::with('owner', 'customer')->where('id', $tenant_id)->first();

            if ($tenant_details) {
                // get subscription ids
                $new_plan_id = $request->input('plan');
                $current_plan_id = $tenant_details->customer->subscription('default')->items[0]->stripe_price;
                if ($current_plan_id == $new_plan_id) {
                    return response()->json([
                        'success'        => false,
                        'message'  => 'Selected subscription plan is invalid!'
                    ], 200);
                } else {
                    $current_plan = [];
                    $new_plan = [];
                    // get all plans
                    $plans = $this->subscriptionServices->retrievePlans();
                    if (is_array($plans)) {
                        foreach ($plans as $plan) {
                            if ($current_plan_id == $plan->id) {
                                $current_plan = $plan;
                            } elseif ($new_plan_id == $plan->id) {
                                $new_plan = $plan;
                            }
                        }
                        // check if new plan price is greater than current one
                        if ($new_plan->amount > $current_plan->amount) {
                            // update database
                            $tenant_details_update = DB::table('tenants')->where('id', $tenant_id)->update(['upgrade_stripe_price' => $new_plan_id]);
                            if ($tenant_details_update) {
                                // send email to user
                                $user_details = User::where('tenant_id', $tenant_id)->where('user_role_id', User::TENANT_OWNER)->first();
                                $send_grid_obj = new SendGridServices($tenant_id);
                                $send_grid_obj->sendTenantUpgradePlanMail($user_details->full_name, $user_details->email, $tenant_details->tenant_name, $new_plan->product->name);
                                return response()->json([
                                    'success'        => true,
                                    'message'  => 'Sent the email successfully to the selected Tenant owner!'
                                ], 200);
                            } else {
                                return response()->json([
                                    'success'        => true,
                                    'message'  => 'Already Sent Email!'
                                ], 200);
                            }
                        } else {
                            return response()->json([
                                'success'        => false,
                                'message'  => 'Selected subscription plan is invalid!'
                            ], 200);
                        }
                    } else {
                        return response()->json([
                            'success'        => true,
                            'message'  => 'Invalid Subscription plans!'
                        ], 200);
                    }
                }
            } else {
                return response()->json([
                    'success'        => false,
                    'message'  => 'Selected Tenant is invalid.'
                ], 200);
            }
        } catch (Exception $e) {
            $message = $e->getMessage() ?? "Invalid Send Mail!";
            return response()->json([
                'success'        => false,
                'message'  => $message
            ], 200);
        }
    }

    /**
     * [GET] Export Tenants Usage CSV
     */
    public function exportTenantsUsageCsv(Request $request)
    {
        $service = new ExportService();
        return $service->exportTenantsUsageCsv($request);
    }


    /**
     * [POST] Update Tenant Name
     */
    public function updateTenantName(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'edit_tenant_name'       => 'required',
            ]);
            if ($validator->fails()) {
                $message = "Validation Failed! ";
                foreach ($validator->errors()->all() as $key => $value) {
                    $message .= $value . " ";
                }
                return redirect()->back()->with('error', $message);
            }

            $id = $request->edit_tenant_id;
            $new_tenant_name = $request->edit_tenant_name;
            $is_exist = Tenant::where('tenant_name', $new_tenant_name)->where('id', '!=', $id)->exists();
            if ($is_exist) {
                return redirect()->back()->with('error', 'This tenant name already exist!');
            }
            $tenant = Tenant::find($id);
            $old_tenant_name = $tenant->tenant_name;
            if ($old_tenant_name == $new_tenant_name) {
                return redirect()->back()->with('success', 'Old & New tenant name same! Nothing to do!');
            }
            $tenant->tenant_name = $new_tenant_name;
            $tenant->save();

            if (config('app.env') == 'local') {
                $http = "http://";
            } else {
                $http = "https://";
            }
            $domainName = config('app.domain');

            $old_tenant_url = $http . $old_tenant_name . '.' . $domainName;
            $new_tenant_url = $http . $new_tenant_name . '.' . $domainName;

            $apiurl = config('services.fv.default_api_base_url');
            if (isset($tenant->fv_api_base_url) and !empty($tenant->fv_api_base_url)) {
                $apiurl = $tenant->fv_api_base_url;
            }
            $fv_service = new FilevineService($apiurl, "", $id);

            $get_all_subscriptions = json_decode($fv_service->getSubscriptionsList());

            foreach ($get_all_subscriptions as $single_subscription) {
                if (strpos(strtolower($single_subscription->endpoint), strtolower($old_tenant_url)) !== false) {
                    $single_subscription->endpoint = str_replace($old_tenant_url, $new_tenant_url, $single_subscription->endpoint);
                    $fv_service->updateSubscription($single_subscription->subscriptionId, $single_subscription);
                }
            }

            return redirect()->back()->with('success', 'Successfully Updated!');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e);
        }
    }


    /**
     * [GET] Get all the billing plan for a tenant
     */
    public function getBillingPlan(Request $request)
    {
        try {
            $tenant_name = $request->input('tenant_name');
            $tenant_id = $request->input('tenant_id');
            $data = $this->subscriptionServices->showSubscription($tenant_name, $tenant_id);
            $response_html = "";

            if (isset($data['plans'])) {
                foreach ($data['plans'] as $key => $plan) :
                    $response_html .= '<div class="col-md-4 col-xxl-4 ">';
                    $response_html .= '<div class="mt-2  bg-white rounded-left shadow-sm">';
                    $response_html .= '<label for="changeplan-item-' . $plan->id . '" class="selected-label first-tab">';
                    $response_html .= '<input type="hidden" name="plan_tenant_id" value="' . $tenant_id . '"/>';
                    $response_html .= '<input type="radio" name="change_plan" id="changeplan-item-' . $plan->id . '" value="' . $plan->id . '">';
                    $response_html .= '<span class="icon"></span>';
                    $response_html .= '<div class="pt-25 pb-25 pb-md-10 px-4">';
                    $response_html .= '<h4 class="mb-15">' . $plan->product->name . '</h4>';
                    $response_html .= '<span class="px-7 py-3 d-inline-flex flex-center rounded-lg mb-15 bg-primary-o-10"> <span class="pr-2 opacity-70">$</span>';
                    $response_html .= '<span class="pr-2 font-size-h1 font-weight-bold">' . $plan->usd_amount . '</span>';
                    $response_html .= '<span class="opacity-70">/&nbsp;&nbsp;' . $plan->interval  . '</span>';
                    $response_html .= '</span><br>';
                    $response_html .= '<p class="mb-10 d-flex flex-column text-dark-50"> <span>' . $plan->product->description . '</span></p>';
                    $response_html .= '<span class="btn btn-primary text-uppercase font-weight-bolder px-15 py-3">Select Plan</span>';
                    $response_html .=  '</div> </label></div></div>';
                endforeach;
            }
            return response()->json([
                'success'        => true,
                'response_html' => $response_html,
                'message'  => 'Successful!'
            ], 200);
            return $response_html;
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e);
        }
    }
}
