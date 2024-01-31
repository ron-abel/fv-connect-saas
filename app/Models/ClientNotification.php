<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientNotification extends Model
{
    use HasFactory;

    protected $table = 'client_notifications';

    protected $fillable = [
        'tenant_id',
        'start_date',
        'end_date',
        'notice_body',
        'banner_color',
        'is_active'
    ];
}
