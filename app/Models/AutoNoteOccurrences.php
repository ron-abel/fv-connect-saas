<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutoNoteOccurrences extends Model
{
    use HasFactory;

    protected $table = "auto_note_occurrences";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_id',
        'status',
        'is_live'
    ];
}
