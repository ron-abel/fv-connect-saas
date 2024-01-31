<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutoNoteGoogleReviewCities extends Model
{
    use HasFactory;

    protected $table = "auto_note_google_review_cities";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'auto_note_google_review_link_id',
        'zip_code',
    ];
}
