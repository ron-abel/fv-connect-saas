<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MassMessageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'mass_message_id',
        'person_name',
        'person_number',
        'job_id',
        'from_number',
        'is_inbound',
        'sent_at',
        'is_sent',
        'note',
        'failed_at',
        'failed_count'
    ];

    public function mass_message()
    {
        return $this->belongsTo(MassMessage::class);
    }
}
