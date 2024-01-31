<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutoNotePhases extends Model
{
    use HasFactory;

    protected $table = "auto_note_phases";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_id',
        'fv_project_type_id',
        'fv_project_type_name',
        'phase_change_type',
        'phase_name',
        'fv_phase_id',
        'is_active',
        'custom_message',
        'is_send_google_review'
    ];
}
