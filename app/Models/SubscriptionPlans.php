<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Billable;

class SubscriptionPlans extends Model
{
    use HasFactory;
    use Billable;
    protected $table = "subscription_plans";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'stripe_plan_id',
        'stripe_product_id',
        'plan_name',
        'plan_description', 
        'plan_price',
        'plan_interval',
        'plan_trial_days', 
        'plan_is_active	', 
        'plan_is_default', 
        'plan_tenant_id', 
    ];
}
