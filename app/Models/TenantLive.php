<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantLive extends Model
{
    use HasFactory;

    protected $table = "tenant_lives";

    protected $guarded = ['id'];
}
