<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Templates extends Model
{
    use HasFactory;

    protected $table = "templates";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'template_name',
        'template_description',
        'tenant_id',
        'is_default',
    ];

    public function tenant()
    {
        return $this->hasOne(Tenant::class, 'id', 'tenant_id');
    }
}