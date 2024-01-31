<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class AutomatedWorkflowLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    protected $table = 'automated_workflow_logs';

    public static function saveLog($tenant_id, $trigger_id, $json_request)
    {
        $automatedWorkflowLog = new AutomatedWorkflowLog;
        $automatedWorkflowLog->tenant_id = $tenant_id;
        $automatedWorkflowLog->trigger_id = $trigger_id;
        $automatedWorkflowLog->webhook_request_json = $json_request;
        $automatedWorkflowLog->save();
        return $automatedWorkflowLog->id;
    }
}
