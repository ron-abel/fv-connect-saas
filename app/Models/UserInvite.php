<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInvite extends Model
{
    use HasFactory;

    protected $table = "user_invites";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_id',
        'user_role_id',
        'user_id',
        'email',
        'is_registered',
        'token'
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function user_role()
    {
        return $this->hasOne(UserRole::class, 'id', 'user_role_id');
    }
}
