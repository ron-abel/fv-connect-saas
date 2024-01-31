<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $table = "log";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_id',
        'Lookup_IP',
        'Lookup_Name',
        'Lookup_Phone_num',
        'Lookup_Project_Id',
        'Result_Client_Name',
        'Result_Project_Id',
        'Result',
        'note',
        'fv_client_id',
        'lookup_email'
    ];
}
