<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookSettings extends Model
{
    use HasFactory;

    protected $table = "webhook_settings";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_id',
        'trigger_action_name',
        'filevine_hook_url',
        'delivery_hook_url',
        'item_change_type',
        'phase_change_event',
        'task_changed',
        'collection_changed',
        'fv_phase_id'
    ];
}
