<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigCustomProjectName extends Model
{
    use HasFactory;

    protected $table = "config_custom_project_names";

    protected $fillable = [
        'tenant_id',
        'selected_option',
        'fv_project_type_id',
        'fv_project_type_name',
        'fv_section_id',
        'fv_section_name',
        'fv_field_id',
        'fv_field_name',
        'sec_fv_project_type_id',
        'sec_fv_project_type_name',
        'sec_fv_section_id',
        'sec_fv_section_name',
        'sec_fv_field_id',
        'sec_fv_field_name'
    ];
}
