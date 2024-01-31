<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalteamConfig extends Model
{
    use HasFactory;

    protected $table = "legalteam_configs";

    /**
     * Type
     */
    const TYPE_FETCH = 'fetch';
    const TYPE_STATIC = 'static';

    /**
     * Checkbox values
     */
    const YES = 1;
    const NO = 0;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_id',
        'type',
        'role_title',
        'fv_role_id',
        'name',
        'email',
        'phone',
        'is_follower_required',
        'is_enable_feedback',
        'is_enable_email',
        'is_active',
        'role_order',
    ];

    public static $legalteam_config_types = [
        'Paralegal'              => 'Paralegal',
        'Assistant'              => 'Assistant',
        'Attorney'               => 'Attorney',
        'ClientRelationsManager' => 'Client Relations Manager'
    ];
}
