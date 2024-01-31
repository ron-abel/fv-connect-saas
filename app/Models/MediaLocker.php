<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaLocker extends Model
{
    use HasFactory;

    protected $table = 'media_locker';

    protected $fillable = [
        'tenant_id',
        'media_code',
        'media_url'
    ];
}
