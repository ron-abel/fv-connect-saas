<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FvClients extends Model
{
    use HasFactory;

    protected $table = "fv_clients";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_id',
        'fv_client_id',
        'fv_client_name',
        'fv_client_address',
        'fv_client_zip',
        'is_google_review_response'
    ];
}
