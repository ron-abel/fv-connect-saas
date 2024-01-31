<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutoNoteGoogleReviewLinks extends Model
{
    use HasFactory;

    protected $table = "auto_note_google_review_links";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_id',
        'review_link',
        'handle_type',
        'is_default',
        'description',
    ];
}
