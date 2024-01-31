<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Billable;

class Subscription extends Model
{
    use HasFactory;
    use Billable;
    protected $table = "subscriptions";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'subscription_customer_id',
        'name',
        'stripe_id',
        'stripe_status', 
        'stripe_price',
        'quantity',
        'trial_ends_at', 
        'ends_at	', 
        'cancel_at',
        'needs_cancelled_at'
    ];
}
