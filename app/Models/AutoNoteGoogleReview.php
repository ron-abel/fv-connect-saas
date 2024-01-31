<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutoNoteGoogleReview extends Model
{
    use HasFactory;

    protected $table = "auto_note_google_reviews";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_id',
        'is_set_qualified_response_threshold',
        'minimum_score',
        'qualified_review_request_msg_body',
        'is_send_unqualified_response_request',
        'unqualified_review_request_msg_body',
        'review_request_text_body',
    ];
}
