<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FvClientPhones extends Model
{
    use HasFactory;

    protected $table = "fv_client_phones";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_id',
        'client_phone',
        'client_phone_state',
        'client_phone_timezone',
        'first_sms_sent_at',
        'auto_communication_stop_at',
        'auto_communication_start_at'
    ];
}
