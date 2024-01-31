<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;


class AutomatedWorkflowTriggerActionMapping extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    protected $table = 'automated_workflow_trigger_action_mappings';


    public static function getActionDetails($tenant_id, $trigger_id)
    {
        return DB::table('automated_workflow_actions')->join('automated_workflow_initial_actions', 'automated_workflow_initial_actions.id', '=', 'automated_workflow_actions.automated_workflow_initial_action_id')
            ->join('automated_workflow_trigger_action_mappings', 'automated_workflow_actions.id', '=', 'automated_workflow_trigger_action_mappings.action_id')
            ->join('automated_workflow_triggers', 'automated_workflow_triggers.id', '=', 'automated_workflow_trigger_action_mappings.trigger_id')
            ->where('automated_workflow_actions.tenant_id', $tenant_id)
            ->where('automated_workflow_initial_actions.tenant_id', $tenant_id)
            ->where('automated_workflow_trigger_action_mappings.tenant_id', $tenant_id)
            ->where('automated_workflow_trigger_action_mappings.trigger_id', $trigger_id)
            ->where('automated_workflow_triggers.id', $trigger_id)
            ->where('automated_workflow_triggers.tenant_id', $tenant_id)
            ->whereNull('automated_workflow_trigger_action_mappings.deleted_at')
            ->select('automated_workflow_actions.*', 'automated_workflow_initial_actions.action_short_code', 'automated_workflow_initial_actions.action_name as ini_action_name', 'automated_workflow_trigger_action_mappings.trigger_id', 'automated_workflow_triggers.trigger_name','automated_workflow_trigger_action_mappings.status as status')
            ->get();
    }
}
