<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookLogs extends Model
{
    use HasFactory;

    protected $table = "webhook_logs";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_id',
        'trigger_action_name',
        'phase_change_event',
        'item_change_type',
        'fv_personId',
        'fv_projectId',
        'fv_org_id',
        'fv_userId',
        'fv_phaseId',
        'fv_phaseName',
        'is_handled',
        'fv_object_id',
        'fv_object',
        'fv_event',
        'webhook_route'
    ];
}
