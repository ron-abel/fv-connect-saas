<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public const SUPERADMIN = 1;
    public const TENANT_MANAGER = 2;
    public const TENANT_OWNER = 3;
    public const TENANT_SUPPORTER = 4;
    public const TENANT_VIEWER = 5;

    protected $fillable = [
        'name',
        'full_name',
        'email',
        'password',
        'user_role_id',
        'tenant_id',
        'remember_token',
        'email_verified_at',
        'admin_token',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
