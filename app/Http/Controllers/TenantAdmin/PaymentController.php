<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as Logging;

class PaymentController extends Controller
{
    public $cur_tenant_id;
    public function __construct()
    {
        Controller::setSubDomainName();
        $this->cur_tenant_id = session()->get('tenant_id');
    }


    public function showPaymentHistory()
    {
        try {
            $subscribed_customers = SubscriptionCustomer::where('tenant_id', $this->cur_tenant_id)->get();
            $key = \config('services.stripe.secret');
            if ($key == '') {
                return 'error';
            }
            $data = [];
            $stripe  = new \Stripe\StripeClient($key);
            foreach ($subscribed_customers as $subscribed_customer) {
                if ($subscribed_customer != [] && $subscribed_customer->subscribed('default')) {
                    $subscriptions = $subscribed_customer->subscriptions()->active()->get();
                    $products = [];
                    foreach ($subscriptions as $s) {
                        $item = $s->items->first();
                        $products[] = $stripe->products->retrieve(
                            $item->stripe_product,
                            []
                        );
                    }
                    array_push($data, [
                        'id' => $subscribed_customer->id,
                        'user_id' => $subscribed_customer->user_id,
                        'customer_name' => $subscribed_customer->customer_name,
                        'stripe_id' => $subscribed_customer->stripe_id,
                        'pm_type' => $subscribed_customer->pm_type,
                        'pm_last_four' => $subscribed_customer->pm_last_four,
                        'products' => $products,
                        'stripe_customer' =>  $subscribed_customer->asStripeCustomer()
                    ]);
                }
            }
            return $this->_loadContent('admin.pages.payment_history', compact('data', 'stripe'));
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
}
