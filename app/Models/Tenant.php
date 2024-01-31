<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Billable;
use App\Models\SubscriptionCustomer;
use App\Models\Log;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log as Logging;

class Tenant extends Model
{
    use HasFactory;
    use Billable;
    use SoftDeletes;
    protected $table = "tenants";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_name',
        'tenant_law_firm_name',
        'fv_tenant_base_url',
        'fv_api_base_url',
        'tenant_description',
        'is_active',
        'upgrade_stripe_price',
        'is_verified',
        'is_accept_license',
        'fv_report_id',
        'fv_project_count'
    ];

    protected $appends = ['status', 'usage_stats'];
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function user_invites()
    {
        return $this->hasMany(UserInvite::class);
    }

    public function owner()
    {
        return $this->hasOne(User::class, 'tenant_id', 'id')->where('user_role_id', User::TENANT_OWNER);
    }

    public function customer()
    {
        return $this->hasOne(SubscriptionCustomer::class, 'tenant_id', 'id')->latest();
    }

    public function getStatusAttribute()
    {
        $status = 'Unverified';
        $user = User::where('tenant_id', $this->id)->first();
        if ($user) {
            $subs = SubscriptionCustomer::where('tenant_id', $this->id)->first();
            if ($subs && $subs->is_active) {
                $status = 'Billing Completed';
            } elseif ($subs && !$subs->is_active) {
                $status = "Billing Canceled";
                if ($subs->is_expired) {
                    $status = "Billing Expired";
                }
            } elseif ($this->is_verified) {
                $status = 'Verified';
            } else {
                $status = "Unverified";
            }
        }
        return $status;
    }

    public function getUsageStatsAttribute()
    {
        try {
            $response = [
                'api_usage' => "",
                'api_usage_per_day' => "",
                'twilio_aggregated_cost' => ""
            ];
            if (isset($this->customer) && $this->customer->subscribed('default')) {
                $start_obj = new \DateTime($this->customer->subscription('default')->created_at);

                // $end_obj = new \DateTime($this->customer->subscription('default')->ends_at);
                $end_obj = new \DateTime();

                $diff_days = $end_obj->diff($start_obj)->days;
                if ($diff_days <= 0) {
                    $diff_days = 1;
                }
                $billing_start = $start_obj->format('Y-m-d');
                $billing_end = $end_obj->format('Y-m-d 23:59:59');
                // get count from logs table
                $logs = Log::where('tenant_id', $this->id)
                    ->where('created_at', '>=', $billing_start)
                    ->where('created_at', '<', $billing_end)
                    ->count();

                $api_usage_per_day = "";
                if ($diff_days > 30) {
                    $end_obj = $end_obj->modify('-30 days');
                    $billing_start = $end_obj->format('Y-m-d');
                    $logs30 = Log::where('tenant_id', $this->id)
                        ->where('created_at', '>=', $billing_start)
                        ->where('created_at', '<', $billing_end)
                        ->count();
                    $api_usage_per_day = $logs30 > 0 ? number_format($logs30 / 30, 4) : "";
                } else {
                    $api_usage_per_day = $logs > 0 ? number_format($logs / $diff_days, 4) : "";
                }

                $response = [
                    'tenant' => $this->id,
                    'api_usage' => $logs > 0 ? $logs : "",
                    'api_usage_per_day' => $api_usage_per_day,
                    'twilio_aggregated_cost' => $logs > 0 ? number_format($logs * config('twilio.twilio.cost_per_sms'), 4) : ""
                ];
            }
            return $response;
        } catch (\Exception $ex) {
            Logging::warning($ex->getMessage());
        }
    }
}
