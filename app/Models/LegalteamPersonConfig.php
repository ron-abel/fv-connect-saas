<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalteamPersonConfig extends Model
{
    use HasFactory;

    protected $table = "legalteam_person_configs";
    const TYPE_FETCH = 'fetch';
    const TYPE_STATIC = 'static';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_id',
        'fv_project_type_id',
        'fv_project_type_name',
        'fv_section_id',
        'fv_section_name',
        'fv_person_field_id',
        'fv_person_field_name',
        'is_enable_phone',
        'is_enable_email',
        'is_enable_feedback',
        'is_static_name',
        'override_name',
        'is_override_phone',
        'override_phone',
        'is_override_email',
        'override_email',
        'sort_order',
        'type',
        'name',
        'email',
        'phone',
        'role_title'
    ];
}
