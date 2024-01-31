<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionCoupon extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "subscription_coupons";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'subscription_id',
        'stripe_subscription_id',
        'coupon_id',
        'stripe_coupon_id',
    ];
}
