<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedbackNoteManager extends Model
{
    use HasFactory;

    protected $table = 'feedback_note_managers';

    protected $guarded = [];
}
