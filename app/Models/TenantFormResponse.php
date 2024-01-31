<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantFormResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_form_id',
        'fv_client_id',
        'fv_project_id',
        'form_response_values_json',
        'error_log',
        'fv_project_name',
        'fv_client_name'
    ];
}
