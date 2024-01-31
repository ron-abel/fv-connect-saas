<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MassMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'created_at',
        'created_by',
        'is_fetch',
        'is_upload_csv',
        'fv_person_type_id',
        'fv_person_type_name',
        'is_exclude_blacklist',
        'upload_csv_file_name',
        'message_body',
        'note',
        'is_complete',
        'is_schedule_job',
        'schedule_time'
    ];

    protected $appends = ['progress'];

    public function getProgressAttribute()
    {
        $mass_message_logs = $this->mass_message_logs();
        $progress  = $mass_message_logs->count() / 100;

        return round($mass_message_logs->where('is_sent', true)->count() / $progress, 1);
    }

    public function mass_message_logs()
    {
        return $this->hasMany(MassMessageLog::class);
    }
}
