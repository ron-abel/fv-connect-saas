<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LanguageLog extends Model
{
    use HasFactory;

    protected $table = 'fv_client_language_logs';

    public $timestamps = true;

    protected $fillable = [
        'tenant_id',
        'fv_client_id',
        'client_ip',
        'language',
        'updated_at'
    ];
}
