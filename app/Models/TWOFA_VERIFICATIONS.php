<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TWOFA_VERIFICATIONS extends Model
{
    use HasFactory;
    protected $table = "twofa_verifications";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'service_sid',
        'phone',
        'code',
        'tries',
        'status'
    ];
}
