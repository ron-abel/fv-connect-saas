<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MassEmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'mass_email_id',
        'person_name',
        'person_email',
        'cc_email',
        'job_id',
        'sent_at',
        'is_sent',
        'note',
        'failed_at',
        'failed_count'
    ];

    public function mass_email()
    {
        return $this->belongsTo(MassEmail::class);
    }
}
