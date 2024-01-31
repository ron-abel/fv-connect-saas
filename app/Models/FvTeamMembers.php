<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FvTeamMembers extends Model
{
    use HasFactory;

    protected $table = "fv_team_members";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_id',
        'fv_user_id',
        'user_name',
        'first_name',
        'last_name',
        'email',
        'full_name',
        'level',
        'team_org_roles',
        'picture_url',
        's3_image_url',
        'is_primary',
        'is_admin',
        'is_first_primary',
        'is_only_primary',
    ];
}