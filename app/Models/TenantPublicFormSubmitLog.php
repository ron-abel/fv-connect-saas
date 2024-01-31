<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TenantPublicFormSubmitLog extends Model
{
    use HasFactory;

    protected $table = "tenant_form_public_submit_logs";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'form_id',
        'user_ip',
        'device_id'
    ];

    public function getCreatedAtAttribute($value)
    {
        $timezone = 'EST';
        return Carbon::createFromTimestamp(strtotime($value))
            ->timezone($timezone)
            // Leave this part off if you want to keep the property as 
            // a Carbon object rather than always just returning a string
            // ->toDateTimeString()
        ;
    }
}