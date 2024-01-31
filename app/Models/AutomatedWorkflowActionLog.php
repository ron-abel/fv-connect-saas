<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class AutomatedWorkflowActionLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'tenant_id',
        'automated_workflow_log_id',
        'trigger_id',
        'action_id',
        'fv_project_id',
        'fv_client_id',
        'emails',
        'sms_phones',
        'note_body',
        'is_handled'
    ];
}
