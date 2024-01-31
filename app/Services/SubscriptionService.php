<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Exception;

use App\Models\SubscriptionCustomer;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\SubscriptionPlans;
use App\Models\StripeCoupon;
use App\Models\SubscriptionCoupon;
use App\Models\SubscriptionPlanMapping;
use App\Services\FVInitializerService;

class SubscriptionService
{
    public $stripe;

    public function __construct()
    {
        $key = \config('services.stripe.secret');
        if ($key == '') {
            return 'error';
        }
        $this->stripe  = new \Stripe\StripeClient($key);
    }

    /**
     * [GET] Retrieve all plans from stripe
     */
    public function retrievePlans()
    {
        try {
            $limit = (int)env('STRIPE_GET_PLAN_LIMIT', 99);
            $plansraw = $this->stripe->plans->all(['limit' => $limit]);
            $plans = $plansraw->data;
        } catch (Exception $e) {
            return $e->getMessage();
        }

        $st_plans = [];
        foreach ($plans as $plan) {
            if ($plan->active !== true) {
                continue;
            }
            $prod = $this->stripe->products->retrieve(
                $plan->product,
                []
            );
            if ($prod->active == true) {
                $plan->product = $prod;

                // all plan amounts are charged in cents
                $plan->usd_amount = 0;
                if (isset($plan->amount)) {
                    $plan->usd_amount = $plan->amount / 100;
                }

                $st_plans[] = $plan;
            }
        }
        return $st_plans;
    }

    /**
     * [GET] Retrieve all Total plans from stripe
     */
    public function retrieveTotalPlans()
    {
        try {
            $limit = (int)env('STRIPE_GET_PLAN_LIMIT', 99);
            $plansraw = $this->stripe->plans->all(['limit' => $limit]);
            $plans = $plansraw->data;
        } catch (Exception $e) {
            return $e->getMessage();
        }

        $st_plans = [];
        foreach ($plans as $plan) {
            $prod = $this->stripe->products->retrieve(
                $plan->product,
                []
            );
            $plan->product = $prod;

            // all plan amounts are charged in cents
            $plan->usd_amount = 0;
            if (isset($plan->amount)) {
                $plan->usd_amount = $plan->amount / 100;
            }

            $st_plans[] = $plan;
        }
        return $st_plans;
    }

    /**
     * [GET] Retrieve plans by tanant name
     */
    public function retrievePlansByTenant($tenant_name)
    {
        try {
            $limit = (int)env('STRIPE_GET_PLAN_LIMIT', 99);
            $plansraw = $this->stripe->plans->all(['limit' => $limit]);
            $plans = $plansraw->data;
            
            // get all tenant names.
            $tenant_names = Tenant::get()->pluck('tenant_name')->toArray();

            // check if the tenant has the custom plan. 
            $sel_tenant = Tenant::where('tenant_name', $tenant_name)->first();
            $sel_plan = null;
            if(isset($sel_tenant['id'])){
                $sel_plan = SubscriptionPlans::where('plan_tenant_id', $sel_tenant['id'])->first();
            }

            // active plans
            $default_plan_prod_ids = SubscriptionPlans::where('plan_is_active', 1)->where('plan_is_default', 1)->get()->pluck('stripe_product_id')->toArray();

            $st_plans = [];
            $tenant_st_plans = [];
            foreach ($plans as $plan) {
                if ($plan->active !== true) {
                    continue;
                }
                $prod = $this->stripe->products->retrieve(
                    $plan->product,
                    []
                );

                if ($prod->active == true) {
                    $plan->product = $prod;

                    // all plan amounts are charged in cents from stripe, need to convert it to USD
                    $plan->usd_amount = 0;
                    if (isset($plan->amount)) {
                        $plan->usd_amount = $plan->amount / 100;
                    }

                    $st_prod_comp_name = str_replace(' ','', strtolower($plan->product->name));
                    if (
                        $st_prod_comp_name == $tenant_name || 
                        isset($sel_plan['stripe_product_id'], $plan->product->id) && $sel_plan['stripe_product_id'] == $plan->product->id
                    ) {
                        // get the tenant own plans.
                        $tenant_st_plans[] = $plan;
                        break;
                    } else {
                        // get the default plans
                        if (
                            !in_array($st_prod_comp_name, $tenant_names) && 
                            in_array($plan->product->id, $default_plan_prod_ids)
                        ) {
                            $st_plans[] = $plan;
                        }
                    }
                }
            }
            if (!empty($tenant_st_plans)) {
                return $tenant_st_plans;
            }
            return $st_plans;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * Help Function.
     * Get the total project count of the tenant
     */
    public function _getTotalFvProjectCount($tenant_id){
        try {
            $fv_total_project = 0;

            // // Get Project Count For This Tenant
            // $Tenant = Tenant::find($tenant_id);
                
            // $apiurl = config('services.fv.default_api_base_url');
            // if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
            //     $apiurl = $Tenant->fv_api_base_url;
            // }
            // $filevine_api = new FilevineService($apiurl, "");
            
            
            // if ($filevine_api->verify_api_and_session()) {
            //     $offset = 0;
            //     $limit = 1000;
            //     do {
            //         $projects_object = json_decode($filevine_api->getProjectsList($limit, $offset));
            //         if (isset($projects_object->items)) {
            //             $project_items = collect($projects_object->items);
            //             $project_active_count = count($project_items->where('isArchived', false));
            //             $fv_total_project += $project_active_count;
            //         }
            //         $hasMore = isset($projects_object->hasMore) ? $projects_object->hasMore : false;
            //         $offset += $limit;
            //     } while ($hasMore);
            // }

            $fv_total_project = (new FVInitializerService())->getTenantReport($tenant_id);

            return $fv_total_project;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * [GET] Set all data for billing
     */
    public function showSubscription($tenant_name, $tenant_id)
    {
        try {
            $res_data = [
                'error' => true,
                'errorMessage' => "Invalid Stripe Key, Please check your env",
                'plans' => [],
                'user' => [],
                'userProd' => ''
            ];
            $plans = $this->retrievePlansByTenant($tenant_name);
            
            if ($plans == 'error') {
                return $res_data;
            }

            $custom_default_plans = [];
            $fv_total_project = 0;

            $custom_plan_by_tenant_id = SubscriptionPlans::where('plan_is_default', 0)
                ->where('plan_tenant_id', $tenant_id)
                ->where('plan_is_active', 1)
                ->first();

            if ($custom_plan_by_tenant_id) {
                // tenant own custom plan case
                foreach ($plans as $plan) {
                    if ($plan->id == $custom_plan_by_tenant_id->stripe_plan_id) {
                        $custom_default_plans[] = $plan;
                    }
                }
            } else {

                $tenant_plan_id = null;

                // check the if the tenant has purchased plan now. 
                $tenant_subscripion_customer = SubscriptionCustomer::where('tenant_id', $tenant_id)
                    ->where('is_active', 1)
                    ->orderBy('id', 'DESC')
                    ->first();

                if(isset($tenant_subscripion_customer['id'])){
                    // when exist the active subscription of the tenant.
                    $tenant_subscription = Subscription::where('subscription_customer_id', $tenant_subscripion_customer['id'])
                        ->where('stripe_status', 'active')
                        ->orderBy('id', 'desc')
                        ->first();
                    if(isset($tenant_subscription['stripe_price'])){
                        $tenant_plan_id = $tenant_subscription['stripe_price'];

                        $all_custom_plans[] = $tenant_plan_id;
                    }
                }

                if($tenant_plan_id == null){
                    // get the default plans to be showing in the selection.

                    // Get Project Count For This Tenant
                    $fv_total_project = $this->_getTotalFvProjectCount($tenant_id);

                    // Get Plan Based on Project Count
                    $subscription_plan_ids = SubscriptionPlanMapping::whereRaw("'$fv_total_project' BETWEEN project_count_from AND project_count_to")
                        ->pluck('subscription_plan_id')
                        ->toArray();
                    $all_custom_plans = SubscriptionPlans::whereIn('id', $subscription_plan_ids)->pluck('stripe_plan_id')->toArray();
                }
                
                
                foreach ($plans as $plan) {
                    if (in_array($plan->id, $all_custom_plans)) {
                        $custom_default_plans[] = $plan;
                    }
                }
            }

            // Remove inactive plan from new plan list
            $custom_default_plans_new = [];
            $inactive_plans = SubscriptionPlans::where('plan_is_active', 0)->pluck('stripe_plan_id')->toArray();
            foreach ($custom_default_plans as $plan) {
                if (!in_array($plan->id, $inactive_plans)) {
                    $custom_default_plans_new[] = $plan;
                }
            }

            $tenant = Tenant::find($tenant_id);
            $subdomain = session()->get('subdomain');
            $user = auth()->user();
            $userProd = [];
            $subscribeCustomer = SubscriptionCustomer::where('tenant_id', $tenant->id)->first();
            if ($subscribeCustomer != [] /* && $subscribeCustomer->subscribed('default') */) {

                $subscriptions = $subscribeCustomer->subscriptions()
                    ->active()
                    ->whereIn('stripe_status', ['active', 'trialing'])
                    ->first();
                if (isset($subscriptions->items)) {
                    $item = $subscriptions->items->first();
                    $userProd = $this->stripe->products->retrieve(
                        $item->stripe_product,
                        []
                    ) ?? '';
                }
            }

            $strip_intent = null;
            if ($tenant) {
                $strip_intent = $tenant->createSetupIntent();
            }

            $res_data = [
                'user' => $subscribeCustomer,
                'intent' => $strip_intent,
                'plans' => $custom_default_plans_new,
                'subdomain' => $subdomain,
                'userProd' => $userProd,
                'stripe' => $this->stripe,
                'fv_total_project' => $fv_total_project

            ];
            return $res_data;
        } catch (Exception $e) {
            $res_data['error'] = $e->getMessage();
            return $res_data;
        }
    }


    /**
     * [POST] Process for Subscription form of billing
     */
    public function processSubscription($request, $tenant_id)
    {
        try {
            $tenant = Tenant::find($tenant_id);
            $user = Auth::user();
            $coupon = '';
            if (isset($request['ccoupon'])) {
                $coupon = $request['ccoupon'];
                $db_coupon = $this->getCouponFromDB($coupon);
                if (isset($db_coupon['stripe_coupon_id'])) {
                    $validate_coupon = $this->validateCoupon($db_coupon['stripe_coupon_id']);
                    if (!$validate_coupon) {
                        return ['success' => false, 'message' => 'Invalid Coupon Code!'];
                    }
                    $coupon = $db_coupon['stripe_coupon_id'];
                } else {
                    return ['success' => false, 'message' => 'Invalid Coupon Code!'];
                }
            }
            $subscribeCustomer = SubscriptionCustomer::firstOrCreate(['tenant_id' => $tenant->id], [
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'customer_name' => $request['ccname'],
                'customer_email' => $request['cemail'],
                'address' => $request['caddress'],
                // 'description' => $request['cdescription'],
                'phone' => $request['cphone'],
            ]);

            $oldSubscription = $subscribeCustomer->subscription('default');
            $stripeCustomer = $subscribeCustomer->createOrGetStripeCustomer([
                'name' => $request['ccname'],
                'email' => $request['cemail']
            ]);

            $paymentMethod = $request['payment_method'];
            $subscribeCustomer->addPaymentMethod($paymentMethod);

            $plan = $request['plan'];

            $trial_days = (int)env('STRIPE_FREE_TRIAL_DATE');
            $newSubscription = $subscribeCustomer->newSubscription('default', $plan)
                ->withCoupon($coupon)
                ->trialDays($trial_days)
                ->create($paymentMethod);
            if ($request['is_update']) {
                $oldSubscription->update([
                    'stripe_status' => 'inactive'
                ]);
            }

            $timestamp = $newSubscription->asStripeSubscription()->current_period_end ?? '';
            $formatted_timestamp = Carbon::createFromTimeStamp($timestamp)->format('Y-m-d');
            $newSubscription->trial_ends_at = $formatted_timestamp;
            $newSubscription->ends_at = $formatted_timestamp;
            $newSubscription->save();

            // create a new record in subscription coupons
            if (!empty($coupon)) {
                $stripeCoupon = $db_coupon;
                if ($stripeCoupon) {
                    SubscriptionCoupon::create([
                        'subscription_id' => $newSubscription->id,
                        'stripe_subscription_id' => $newSubscription->stripe_id,
                        'coupon_id' => $stripeCoupon['id'],
                        'stripe_coupon_id' => $stripeCoupon['stripe_coupon_id'],
                    ]);
                }
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [GET] Get all plans with formatting
     *
     */
    public static function getAllPlans()
    {
        try {
            $plans = (new self)->retrievePlans();
            $all_plans = [];
            $max_price = 0;
            if (is_array($plans)) {
                foreach ($plans as $plan) {
                    $all_plans[$plan->id] = ($plan->product->name);
                    if ($max_price < $plan->amount) {
                        $max_price = $plan->amount;
                        $max_price_plan = $plan->id;
                    }
                }
            }
            return $all_plans;
        } catch (Exception $e) {
            return false;
        }
    }

    // Validate Coupon
    public function validateCoupon($coupon)
    {
        try {
            $this->stripe->coupons->retrieve($coupon);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * [POST] Create Plan
     *
     */
    public function createStripeProductPlan($plan_details)
    {
        try {
            $plan_response = "";
            if ($plan_details) {
                $stripe_product = $this->stripe->products->create([
                    'name' => $plan_details['plan_name'],
                    'description' => ($plan_details['plan_description'] && $plan_details['plan_description'] != "") ? $plan_details['plan_description'] : $plan_details['plan_name'],
                ]);

                if ($stripe_product) {
                    $plan_data = array(
                        'amount' => $plan_details['plan_price'] * 100, // charged in cents
                        'currency' => 'usd',
                        'interval' => $plan_details['plan_interval'],
                        'product' => $stripe_product->id,
                    );

                    if ($plan_details['plan_trial_days']) {
                        $plan_data['trial_period_days'] = $plan_details['plan_trial_days'];
                    }

                    $plan_response = $this->stripe->plans->create($plan_data);
                }
            }
            return $plan_response;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [POST] Update Product
     *
     */
    public function updateStripeProduct($product_details)
    {
        try {
            $stripe_product_response = "";
            if ($product_details) {
                $stripe_product_response = $this->stripe->products->update(
                    $product_details['product_id'],
                    [
                        'name' => $product_details['plan_name'],
                        'description' => $product_details['plan_description']
                    ]
                );
            }
            return $stripe_product_response;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [POST] Update Plan
     * create the new plan price with the new price with the same product id.
     *
     */
    public function updateStripePlan($plan_details)
    {
        try {
            $plan_response = "";
            if ($plan_details) {
                $plan_data = array(
                    'amount' => $plan_details['plan_price'] * 100, // charged in Cents
                    'currency' => 'usd',
                    'interval' => $plan_details['plan_interval'],
                    'product' => $plan_details['product_id'],
                );

                if ($plan_details['plan_trial_days']) {
                    $plan_data['trial_period_days'] = $plan_details['plan_trial_days'];
                }

                $plan_response = $this->stripe->plans->create($plan_data);
            }
            return $plan_response;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [POST] Inactivate Plan
     *
     */
    public function inactiveStripePlan($plan_id)
    {
        try {
            $plan_response = "";
            if ($plan_id) {
                $plan_response = $this->stripe->plans->update(
                    $plan_id,
                    ['active' => false]
                );
            }
            return $plan_response;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [GET] Get Subscriptions
     *
     */
    public function getAllActiveSubscription()
    {
        try {
            $subscriptions = [];
            $limit = (int)env('STRIPE_GET_PLAN_LIMIT', 99);
            $starting_after = null;
            
            do {
                $params = ['limit' => $limit];
                if($starting_after != null){
                    $params['starting_after'] = $starting_after;
                }

                $subscription_raw = $this->stripe->subscriptions
                                    ->all($params);
                $has_more = isset($subscription_raw['has_more']) ? $subscription_raw['has_more'] : false;
                
                $starting_after = end($subscription_raw->data);
                
                $subscriptions = array_merge($subscriptions, $subscription_raw->data);
            } while ($has_more);

            return $subscriptions;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [GET] Get All Cancel Subscriptions
     *
     */
    public function getAllCancelSubscription()
    {
        try {
            $subscriptions = [];
            $limit = (int)env('STRIPE_GET_PLAN_LIMIT', 99);
            $starting_after = null;

            do {
                $params = ['limit' => $limit, 'status' => 'canceled'];
                if($starting_after != null){
                    $params['starting_after'] = $starting_after;
                }

                $subscription_raw = $this->stripe->subscriptions
                                    ->all($params);
                $has_more = isset($subscription_raw['has_more']) ? $subscription_raw['has_more'] : false;
                
                $starting_after = end($subscription_raw->data);
                
                $subscriptions = array_merge($subscriptions, $subscription_raw->data);
            } while ($has_more);
            
            return $subscriptions;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [GET] Get All Coupons
     *
     */
    public function getAllCoupons($sync_table = false)
    {
        try {
            $coupons = $this->stripe->promotionCodes->all();
            // check if table needs syncing
            if ($sync_table && is_array($coupons->data) && count($coupons->data) > 0) {
                foreach ($coupons->data as $coupon) {
                    if (isset($coupon->coupon)) {
                        StripeCoupon::updateOrCreate(
                            [
                                'stripe_coupon_id' => $coupon->coupon->id
                            ],
                            [
                                'stripe_coupon_name' => $coupon->code,
                                'stripe_coupon_percent_off' => $coupon->coupon->percent_off,
                                'stripe_coupon_amount' => $coupon->coupon->amount_off,
                                'stripe_coupon_currency' => $coupon->coupon->currency,
                                'stripe_coupon_duration' => $coupon->coupon->duration,
                                'stripe_coupon_duration_in_months' => $coupon->coupon->duration_in_months,
                                'stripe_coupon_livemode' => $coupon->coupon->livemode,
                                'stripe_coupon_valid' => $coupon->coupon->valid
                            ]
                        );
                    }
                }
            }
            return $coupons->data;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * [GET] Get Coupon From Database
     *
     */
    public function getCouponFromDB($code)
    {
        $response = [];
        try {
            $coupon = StripeCoupon::where(function ($q) use ($code) {
                return $q->where('stripe_coupon_name', $code)
                    ->orWhere('stripe_coupon_id', $code);
            })
                ->where('stripe_coupon_valid', 1)
                ->first();

            if (isset($coupon->stripe_coupon_id)) {
                $response = $coupon->toArray();
            }
        } catch (Exception $e) {
        }
        return $response;
    }

    /**
     * [GET] Check If Tenant Has Subscription
     *
     */
    public static function checkIfTenantFirstPayment($tenant_id)
    {
        try {
            $subscribeCustomer = SubscriptionCustomer::where('tenant_id', $tenant_id)->first();
            if (isset($subscribeCustomer->tenant_id)) {
                return false;
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getSubscription($id)
    {
        try {
            $subscription_raw = $this->stripe->subscriptions->retrieve($id);
            return $subscription_raw;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
    /*
     * [GET] Get All Cancel Subscriptions
     *
     */
    public function cancelSubscription($subscription_id)
    {
        try {
            $subscription_raw = $this->stripe->subscriptions->cancel($subscription_id);
            $subscriptions = $subscription_raw->data;
            return $subscriptions;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function cancelSubscriptionCustomDate($subscription_id, $date)
    {
        try {
            $subscription_raw = $this->stripe->subscriptions->update($subscription_id, ['cancel_at' => $date]);
            $subscriptions = $subscription_raw->data;
            return $subscriptions;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
