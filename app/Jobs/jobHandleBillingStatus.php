<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log as Logging;

use App\Services\SubscriptionService;

use App\Models\SubscriptionCustomer;
use App\Models\Tenant;


class jobHandleBillingStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $subscriptionServices;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(SubscriptionService $subscriptionServices)
    {
        $this->subscriptionServices = $subscriptionServices;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Update subscriptions table ends_at
            $active_customer_list = [];
            $subscriptions = $this->subscriptionServices->getAllActiveSubscription();
            if(count($subscriptions) > 0)
            foreach ($subscriptions as $subscription) {
                $active_customer_list[] = $subscription->customer;
                $subscription_details = DB::table('subscriptions')->where('stripe_id', $subscription->id)->first();
                if ($subscription_details != null) {
                    
                    $timestamp = $subscription->current_period_end ?? '';
                    $ends_at = Carbon::createFromTimeStamp($timestamp)->format('Y-m-d');
                    $ends_at = Carbon::createFromFormat('Y-m-d', $ends_at)->endOfDay()->toDateTimeString();
                    $sub_data['ends_at'] = $ends_at;
                    if(!empty($subscription->cancel_at)) {
                        $needs_cancel_at = Carbon::createFromTimeStamp($subscription->cancel_at)->format('Y-m-d');
                        $needs_cancel_at = Carbon::createFromFormat('Y-m-d', $needs_cancel_at)->endOfDay()->toDateTimeString();
                        $sub_data['needs_cancelled_at'] = $needs_cancel_at;
                    }

                    DB::table('subscriptions')->where('stripe_id', $subscription->id)->update($sub_data);
                }
            }

            // Update customer_subscriptions table with cancel
            $canceled_subscriptions = $this->subscriptionServices->getAllCancelSubscription();
            if(count($canceled_subscriptions) > 0)
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

            //Get all active plans
            $all_plans = [];
            $plans = $this->subscriptionServices->retrievePlans();
            if (is_array($plans)) {
                foreach ($plans as $plan) {
                    $all_plans[$plan->product->id] = $plan->id;
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
                        if(in_array($single_tenant->customer->stripe_id , $active_customer_list)){
                            $single_tenant->customer->update([
                                'is_active' => 1,
                                'is_canceled' => 0,
                                'is_expired' => 0
                            ]);
                        }else{
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
        } catch (\Exception $e) {
            Logging::warning($e->getMessage());
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

}
