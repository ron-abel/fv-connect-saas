<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FvNoteComment extends Model
{
    use HasFactory;

    protected $table = "fv_note_comments";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'fv_project_id',
        'fv_note_id',
        'fv_comment_id',
        'fv_comment_body',
        'client_name',
        'client_email'
    ];
}
