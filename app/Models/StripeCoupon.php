<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StripeCoupon extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "stripe_coupons";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'stripe_coupon_id',
        'stripe_coupon_name',
        'stripe_coupon_percent_off',
        'stripe_coupon_amount',
        'stripe_coupon_currency',
        'stripe_coupon_duration',
        'stripe_coupon_duration_in_months',
        'stripe_coupon_livemode',
        'stripe_coupon_valid',
    ];
}
