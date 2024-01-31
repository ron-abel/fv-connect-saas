<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Version extends Model
{
    use HasFactory;

    protected $fillable = [
        'version_name',
        'description',
        'major',
        'minor',
        'patch',
        'full',
        'created_at',
        'updated_at',
    ];
}
