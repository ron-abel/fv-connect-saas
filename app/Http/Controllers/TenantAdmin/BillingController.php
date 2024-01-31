<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionCustomer;
use App\Services\SubscriptionService;
use App\Services\CampaignMonitorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as Logging;

use App\User;
use Illuminate\Support\Facades\Auth;
use Exception;
use Hash;
use App\Models\Tenant;
use App\Models\Config;
use Carbon\Carbon;


class BillingController extends Controller
{
    private $subscriptionService;
    private $cmServices;
    public $cur_tenant_id;

    public function __construct(SubscriptionService $subscriptionService, CampaignMonitorService $cmServices)
    {
        $this->cmServices = $cmServices;
        $this->subscriptionService = $subscriptionService;
        Controller::setSubDomainName();
        $this->cur_tenant_id = session()->get('tenant_id');
    }

    /**
     * [GET] Billing Page for Admin
     */
    public function index($domain, $is_update = 0)
    {
        try {
            $data = $this->subscriptionService->showSubscription($domain, $this->cur_tenant_id);
            $data['fv_total_project'] = isset($data['fv_total_project']) ? $data['fv_total_project'] : 0;
            $data['is_update'] = $is_update;
            $data['tenant'] = Tenant::find($this->cur_tenant_id);
            $data['vineconnect_sales_email'] = config('services.email.vineconnect_sales_email');
            $config_details = Config::where('tenant_id', $this->cur_tenant_id)->first();
            $data['reply_to_email'] = isset($config_details->reply_to_org_email) ? $config_details->reply_to_org_email : '';
            return $this->_loadContent('admin.pages.billing', $data);
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
     * [POST] Submit Subscription or Update Subscription form of billing
     */
    public function submitSubscription(Request $request)
    {
        try {
            $response = $this->subscriptionService->processSubscription($request->all(), $this->cur_tenant_id);
            $auth_user = Auth::user();
            $tenant = Tenant::find($auth_user->tenant_id);
            $request_plan = $request->plan;
            $plans = $this->subscriptionService->retrievePlansByTenant($tenant->tenant_name);
            $billing_amount = 0;
            $billing_plan = "";
            foreach ($plans as $strip_plan) {
                if ($strip_plan->id == $request_plan) {
                    $billing_amount = $strip_plan->amount / 100;
                    $billing_plan = isset($strip_plan->product->name) ? $strip_plan->product->name : '';
                    break;
                }
            }
            $this->cmServices->billingConfiguredTenant([
                'tenant_id' => $auth_user->tenant_id,
                'tenant_name' => $tenant->tenant_name,
                'owner_email' => $auth_user->email,
                'owner_first_name' => $auth_user->full_name,
                'billing_start' => date('Y-m-d H:i:s'),
                'billing_amount' => $billing_amount,
                'billing_plan'  => $billing_plan
            ]);

            if (isset($response['success']) && !$response['success']) {
                return redirect()->back()->withInput()->with('coupon-error', $response['message']);
            }
            if ($request['is_update']) {
                return redirect()->route('billing', ['subdomain' => session()->get('subdomain'), 'is_update' => 0])->with('success', 'Subscription updated Successfully');
            }
            //return redirect()->back()->with('success', 'Setting saved successfully!');
            return redirect()->route('dashboard', [
                'subdomain' => session()->get('subdomain')
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [GET] Cancel Subscription
     */
    public function cancelSubscription($domain, $id)
    {
        try {
            $subscriptionCustomer = SubscriptionCustomer::find($id);
            $timestamp = Carbon::parse($subscriptionCustomer->subscription('default')->ends_at)->addDays(25);
            $subscriptionCustomer->update([
                'is_active' => 0,
                'is_canceled' => 1
            ]);

            // update CM Service info.
            $auth_user = Auth::user();
            $tenant = Tenant::find($auth_user->tenant_id);
            $data = $this->subscriptionService->showSubscription($tenant->tenant_name, $this->cur_tenant_id);
            $billing_amount = $data['stripe']->prices->all(['product' => $data['userProd']->id])->data[0]->unit_amount / 100;
            $this->cmServices->billingCancelledTenant([
                'tenant_id' => $auth_user->tenant_id,
                'tenant_name' => $tenant->tenant_name,
                'owner_email' => $auth_user->email,
                'owner_first_name' => $auth_user->full_name,
                'billing_start' => $subscriptionCustomer->created_at,
                'billing_amount' => $billing_amount
            ]);

            $subscriptionCustomer->subscription('default')->cancelAt($timestamp);

            $subscriptions_id = isset($subscriptionCustomer->subscription('default')->id) ? $subscriptionCustomer->subscription('default')->id : 0;
            if ($subscriptions_id) {
                DB::table('subscriptions')->where('id', $subscriptions_id)->update([
                    'cancel_at' => Carbon::now(),
                    'stripe_status' => 'canceled'
                ]);
            }

            if ($subscriptions_id) {
                // get subscription end date first
                $subscription = DB::table('subscriptions')->where('id', $subscriptions_id)->first();
                DB::table('subscriptions')->where('id', $subscriptions_id)->update([
                    'needs_cancelled_at' => $timestamp
                ]);
            }

            $this->subscriptionService->cancelSubscriptionCustomDate($subscriptionCustomer->plan()->stripe_id, $timestamp->timestamp);

            return redirect()->back()->with('success', 'Subscription Canceled Successfully');
        } catch (Exception $e) {
            $subscriptionCustomer = SubscriptionCustomer::find($id);
            $timestamp = Carbon::parse($subscriptionCustomer->subscription('default')->ends_at)->addDays(30);
            $subscriptionCustomer->is_active = 0;
            $subscriptionCustomer->is_canceled = 1;
            $subscriptionCustomer->save();
            $subscriptionCustomer->subscription('default')->cancelAt($timestamp);
            $subscriptions_id = isset($subscriptionCustomer->subscription('default')->id) ? $subscriptionCustomer->subscription('default')->id : 0;
            if ($subscriptions_id) {
                // get subscription end date first
                $subscription = DB::table('subscriptions')->where('id', $subscriptions_id)->first();
                DB::table('subscriptions')->where('id', $subscriptions_id)->update([
                    'needs_cancelled_at' => $timestamp
                ]);
            }

            return $e->getMessage();
        }
    }

    /**
     * [POST] Update Card
     */
    public function updateCard(Request $request, $domain, $id)
    {
        try {
            $subscriptionCustomer = SubscriptionCustomer::find($id);
            $paymentMethod = $request['payment_method'];
            $subscriptionCustomer->addPaymentMethod($paymentMethod);
            $subscriptionCustomer->updateDefaultPaymentMethod($paymentMethod);
            return redirect()->back()->with('success', 'Setting saved successfully!');
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [POST] Update Plan - Could not find any usage of this function
     */
    public function updatePlan(Request $request, $domain, $id)
    {
        try {
            $subscriptionCustomer = SubscriptionCustomer::find($id);
            $plan = $request['change_plan'];
            $newSubscription = $subscriptionCustomer->subscription('default')->skipTrial()->swap($plan);

            $timestamp = $newSubscription->asStripeSubscription()->current_period_end ?? '';
            $formatted_timestamp = Carbon::createFromTimeStamp($timestamp)->format('Y-m-d');
            $newSubscription->trial_ends_at = $formatted_timestamp;
            $newSubscription->ends_at = $formatted_timestamp;
            $newSubscription->save();

            // update CM service info.
            $auth_user = Auth::user();
            $tenant = Tenant::find($auth_user->tenant_id);
            $plans = $this->subscriptionService->retrievePlansByTenant($tenant->tenant_name);
            $billing_amount = 0;
            $billing_plan = "";
            foreach ($plans as $strip_plan) {
                if ($strip_plan->id == $plan) {
                    $billing_amount = $strip_plan->amount / 100;
                    $billing_plan = isset($strip_plan->product->name) ? $strip_plan->product->name : '';
                    break;
                }
            }
            $this->cmServices->billingConfiguredTenant([
                'tenant_id' => $auth_user->tenant_id,
                'tenant_name' => $tenant->tenant_name,
                'owner_email' => $auth_user->email,
                'owner_first_name' => $auth_user->full_name,
                'billing_start' => date('Y-m-d H:i:s'),
                'billing_amount' => $billing_amount,
                'billing_plan'  => $billing_plan
            ]);

            return redirect()->back()->with('success', 'Plan Successfully Updated');
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [POST] Add Subscription
     */
    public function addSubscription(Request $request, $domain, $id)
    {

        try {
            $subscriptionCustomer = SubscriptionCustomer::find($id);
            $plan = $request['add_subscription_plan'];
            $coupon = '';
            if (isset($request['ccoupon']) && !empty($request['ccoupon'])) {
                $coupon = $request['ccoupon'];
                // get coupon from db first
                $db_coupon = $this->subscriptionService->getCouponFromDB($coupon);
                if (isset($db_coupon['stripe_coupon_id'])) {
                    $validate_coupon = $this->subscriptionService->validateCoupon($db_coupon['stripe_coupon_id']);
                    if (!$validate_coupon) {
                        return redirect()->back()->with('coupon-error-1', 'Invalid Coupon Code!');
                    }
                } else {
                    return redirect()->back()->with('coupon-error-1', 'Invalid Coupon Code!');
                }
            }

            if ($request['default_payment_method']) {
                $newSubscription = $subscriptionCustomer->newSubscription('default', $plan)->withCoupon($coupon)->add();
            } else {
                $paymentMethod = $request['payment_method'];
                $newSubscription = $subscriptionCustomer->newSubscription('default', $plan)->create($paymentMethod);
            }

            $subscriptionCustomer->update([
                'is_active' => 1
            ]);

            $timestamp = $newSubscription->asStripeSubscription()->current_period_end ?? '';
            $formatted_timestamp = Carbon::createFromTimeStamp($timestamp)->format('Y-m-d');
            $newSubscription->trial_ends_at = $formatted_timestamp;
            $newSubscription->ends_at = $formatted_timestamp;
            $newSubscription->save();

            // Update CM service info.
            $auth_user = Auth::user();
            $tenant = Tenant::find($auth_user->tenant_id);
            $plans = $this->subscriptionService->retrievePlansByTenant($tenant->tenant_name);
            $billing_amount = 0;
            $billing_plan = "";
            foreach ($plans as $strip_plan) {
                if ($strip_plan->id == $plan) {
                    $billing_amount = $strip_plan->amount / 100;
                    $billing_plan = isset($strip_plan->product->name) ? $strip_plan->product->name : '';
                    break;
                }
            }
            $this->cmServices->billingConfiguredTenant([
                'tenant_id' => $auth_user->tenant_id,
                'tenant_name' => $tenant->tenant_name,
                'owner_email' => $auth_user->email,
                'owner_first_name' => $auth_user->full_name,
                'billing_start' => date('Y-m-d H:i:s'),
                'billing_amount' => $billing_amount,
                'billing_plan'  => $billing_plan
            ]);

            return redirect()->back()->with('success', 'Subscription successfully added');
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
    public function validateCoupon($subdomain, $coupon)
    {
        // get coupon from db first
        $db_coupon = $this->subscriptionService->getCouponFromDB($coupon);
        if (isset($db_coupon['stripe_coupon_id'])) {
            $validate_coupon = $this->subscriptionService->validateCoupon($db_coupon['stripe_coupon_id']);
            if (!$validate_coupon) {
                return response()->json(['success' => false, 'error' => 'Invalid Coupon Code Inner!']);
            }
        } else {
            return response()->json(['success' => false, 'error' => 'Invalid Coupon Code Outer!']);
        }
        return response()->json(['success' => true]);
    }
}
