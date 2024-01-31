<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Billable;

class SubscriptionCustomer extends Model
{
    use HasFactory;
    use Billable;
    protected $table = "subscription_customers";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'tenant_id', 'customer_name', 'customer_email', 'card_expire_date', 'is_active', 'address', 'description', 'phone', 'shipping', 'is_canceled', 'is_expired'
    ];

    public function plan()
    {
        $plan = DB::table('subscriptions')->where('subscription_customer_id', $this->id)->first();
        return $plan;
    }

    /**
     * check if the customer has the active subscriptions 
     */
    public static function checkActiveSubscription($subscription_customer_id)
    {
        try {
            $today = today()->format('Y-m-d');
            return DB::table('subscriptions')
                ->where('subscription_customer_id', $subscription_customer_id)
                ->where('stripe_status', '!=', 'cancel')
                ->where('stripe_status', '!=', 'ended')
                ->where('ends_at', '>=', $today)
                ->exists();
        } catch (\Throwable $th) {
            return false;
        }
        
    }
}
