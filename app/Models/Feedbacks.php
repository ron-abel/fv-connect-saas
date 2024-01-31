<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedbacks extends Model
{
    use HasFactory;

    protected $table = "feedbacks";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_id',
        'project_id',
        'project_name',
        'project_phase',
        'legal_team_email',
        'legal_team_phone',
        'legal_team_name',
        'client_name',
        'client_phone',
        'fd_mark_legal_service',
        'fd_mark_recommend',
        'fd_mark_useful',
        'fd_content'
    ];
}
