<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutoNoteGoogleReviewReplyMessages extends Model
{
    use HasFactory;

    protected $table = "auto_note_google_review_messages";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_id',
        'client_id',
        'message_id',
        'message_body',
        'from_number',
        'msg_type',
        'type_of_line',
        'to_number',
        'is_google_review_filter_msg',
        'is_replied',
        'project_id',
        'note',
    ];
}
