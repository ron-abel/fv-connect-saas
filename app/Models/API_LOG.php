<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class API_LOG extends Model
{
    use HasFactory;
    protected $table = "api_logs";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ip',
        'request_domain',
        'api_name',
        'to_number',
        'fv_project_id',
        'verification_code',
        'user_ip',
        'tenant_id'
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
