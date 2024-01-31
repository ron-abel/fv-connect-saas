<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MassEmail extends Model
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
        'campaign_name',
        'message_body',
        'note',
        'is_complete',
        'is_schedule_job',
        'schedule_time'
    ];

    protected $appends = ['progress'];

    public function getProgressAttribute()
    {
        $mass_email_logs = $this->mass_email_logs();
        $progress = 0;
        if($mass_email_logs->count() > 0) {
            $progress  = $mass_email_logs->count() / 100;
        }
        $logs_count = $mass_email_logs->where('is_sent', true)->count();
        if($logs_count > 0) {
            return round($mass_email_logs->where('is_sent', true)->count() / $progress, 1);
        }
        return 0;
    }

    public function mass_email_logs()
    {
        return $this->hasMany(MassEmailLog::class);
    }
}
