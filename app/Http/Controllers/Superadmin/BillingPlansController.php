<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SubscriptionPlans;
use App\Models\Tenant;
use App\Services\SubscriptionService;
use Auth;
use Exception;
use Illuminate\Support\Facades\Log as Logging;


class BillingPlansController extends Controller
{
    private $subscriptionServices;

    public function __construct(SubscriptionService $subscriptionServices)
    {
        $this->subscriptionServices = $subscriptionServices;
        Controller::setSubDomainName();
    }

    /**
     * [GET] Billing Plans List page for Super Admin
     */
    public function index()
    {
        try {
            $billing_plans = $this->subscriptionServices->retrievePlans();

            foreach ($billing_plans as $plan) {
                if (SubscriptionPlans::where('stripe_plan_id', $plan->id)->first() == null) {
                    $values = array(
                        'stripe_plan_id' => $plan->id,
                        'stripe_product_id' => $plan->product->id,
                        'plan_name' => $plan->product->name,
                        'plan_price' => $plan->usd_amount,
                        'plan_interval' => $plan->interval,
                        'plan_description' => $plan->product->description,
                        'plan_trial_days' => $plan->trial_period_days,
                        'created_at' => date('Y-m-d H:i:s')
                    );
                    SubscriptionPlans::create($values);
                }
            }
            $billing_plans_data = SubscriptionPlans::get();
            return $this->_loadContent('superadmin.pages.billing_plans', ['billing_plans_data' => $billing_plans_data]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [GET] Create Plan for Super Admin
     */
    public function add_billing_plan()
    {
        $tenants_data = Tenant::where('tenant_name', '!=', config('app.superadmin'))->get();
        return $this->_loadContent('superadmin.pages.add_billing_plan', ['tenants_data' => $tenants_data]);
    }

    /**
     * [POST] Create Plan for Super Admin
     */
    public function add_billing_plan_post(Request $request)
    {
        try {
            $request->validate([
                'plan_name' => 'required|string|min:5|unique:subscription_plans,plan_name',
                'plan_price' => 'required|numeric',
                'plan_interval' => 'required|string',
            ]);

            $plan_details = array(
                'plan_name' => $request->input('plan_name'),
                'plan_price' => $request->input('plan_price'),
                'plan_interval' => $request->input('plan_interval'),
                'plan_description' => $request->input('plan_description'),
                'plan_trial_days' => $request->input('plan_trial_days')
            );

            $plan_response = $this->subscriptionServices->createStripeProductPlan($plan_details);

            if ( isset($plan_response,$plan_response->id) ) {
                $current_date = date('Y-m-d H:i:s');
                $plan_is_default = 1;
                if ($request->input('plan_type') == 'custom') {
                    $plan_is_default = 0;
                }

                $plan_is_active = 1;
                if ($request->input('is_active') == 'inactive') {
                    $plan_is_active = 0;
                }

                $values = array(
                    'stripe_plan_id' => $plan_response->id,
                    'stripe_product_id' => $plan_response->product,
                    'plan_name' => $request->input('plan_name'),
                    'plan_price' => $request->input('plan_price'),
                    'plan_interval' => $request->input('plan_interval'),
                    'plan_description' => $request->input('plan_description'),
                    'plan_is_default' => $plan_is_default,
                    'plan_is_active' => $plan_is_active,
                    'plan_tenant_id' => $request->input('tenant_id'),
                    'created_at' => $current_date
                );

                if ($request->input('plan_trial_days')) {
                    $values['plan_trial_days'] = $request->input('plan_trial_days');
                }

                SubscriptionPlans::create($values);
            }else{
                // loging response. 
                $error = [
                    __FILE__,
                    __LINE__,
                    $plan_response
                ];
                Logging::warning(json_encode($error));
            }

            return redirect()->route('billing_plans');
        } catch (\Exception $ex) {
            $error = [
                __FILE__,
                __LINE__,
                $ex->getMessage()
            ];
            Logging::warning(json_encode($error));

            return redirect()->route('billing_plans');
        }
    }

    /**
     * [GET] Edit Plan for Super Admin
     */
    public function edit_billing_plan($billing_plan_id)
    {
        $billing_plan_details =  SubscriptionPlans::where('id', $billing_plan_id)->first();

        if ($billing_plan_details) {
            $tenants_data = Tenant::where('tenant_name', '!=', config('app.superadmin'))->get();
            return $this->_loadContent('superadmin.pages.edit_billing_plans', compact('billing_plan_details', 'tenants_data'));
        } else {
            return abort(404);
        }
    }

    /**
     * [POST] Edit Plan for Super Admin
     */
    public function edit_billing_plan_post($billing_plan_id, Request $request)
    {
        try {
            $current_date = date('Y-m-d H:i:s');

            $billing_plan_details =  SubscriptionPlans::where('id', $billing_plan_id)->first();

            if ($billing_plan_details) {

                if ($billing_plan_details->plan_is_default == 1) {
                    $plan_is_active = 0;
                    if ($request->input('is_active') == 'on') {
                        $plan_is_active = 1;
                    }
                    $values = array(
                        'plan_is_active' => $plan_is_active,
                        'updated_at' => $current_date
                    );

                    $plan_details_update = SubscriptionPlans::where('id', $billing_plan_id)->update($values);
                } else {
                    // custom plan update logic
                    $plan_price = $billing_plan_details->plan_price;
                    $request->validate([
                        'plan_name' => 'required|string|min:5|unique:subscription_plans,plan_name,' . $billing_plan_id . ',id',
                        'plan_price' => 'required',
                    ]);

                    $plan_is_default = 1;
                    if ($request->input('plan_type') == 'custom') {
                        $plan_is_default = 0;
                    }

                    $plan_is_active = 0;
                    if ($request->input('is_active') == 'on') {
                        $plan_is_active = 1;
                    }

                    if ($billing_plan_details->plan_name != $request->input('plan_name') || $billing_plan_details->plan_description != $request->input('plan_description')) {
                        $product_details = array(
                            'product_id' => $billing_plan_details->stripe_product_id,
                            'plan_name'  => $request->input('plan_name'),
                            'plan_description' => $request->input('plan_description'),
                            'plan_is_active' => $plan_is_active
                        );
                        $stripe_product_response = $this->subscriptionServices->updateStripeProduct($product_details);
                    }

                    // new plan price checking.
                    $new_plan_price = $request->input('plan_price');
                    $new_plan_interval = $request->input('plan_interval');
                    $new_plan_trial_days = $request->input('plan_trial_days');

                    $values = array(
                        'plan_name' => $request->input('plan_name'),
                        'plan_price' => $new_plan_price,
                        'plan_description' => $request->input('plan_description'),

                        'plan_interval' => $new_plan_interval,
                        'plan_trial_days' => $new_plan_trial_days,

                        'plan_is_default' => $plan_is_default,
                        'plan_is_active' => $plan_is_active,

                        'plan_tenant_id' => $request->input('tenant_id'),
                        'updated_at' => $current_date
                    );


                    if (isset($new_plan_trial_days, $new_plan_price, $new_plan_interval) && ($new_plan_price != $plan_price || $new_plan_interval != $billing_plan_details->plan_interval || $new_plan_trial_days != $billing_plan_details->plan_trial_days)) {
                        $plan_details = array(
                            'product_id' => $billing_plan_details->stripe_product_id,
                            'plan_price'  => $new_plan_price,
                            'plan_interval' => $new_plan_interval,
                            'plan_trial_days' => $new_plan_trial_days,
                            'plan_is_active' => $plan_is_active
                        );
                        $stripe_plan_response = $this->subscriptionServices->updateStripePlan($plan_details);
                        if ($stripe_plan_response && isset($stripe_plan_response->id)) {
                            $values['stripe_plan_id'] = $stripe_plan_response->id;
                            $inactive_stripe_plan = $this->subscriptionServices->inactiveStripePlan($billing_plan_details->stripe_plan_id);
                        }
                    }

                    $plan_details_update = SubscriptionPlans::where('id', $billing_plan_id)->update($values);
                }

                if ($plan_details_update) {
                    return redirect()->route('edit_billing_plan', ['billing_plan_id' => $billing_plan_id])->with('success', 'Plan Successfully Updated');
                } else {
                    return redirect()->route('edit_billing_plan', ['billing_plan_id' => $billing_plan_id])->with('error', 'No Records Updated');
                }
            } else {
                return abort(404);
            }
        } catch (\Exception $ex) {
            Logging::warning($ex->getMessage());
        }
    }
}
