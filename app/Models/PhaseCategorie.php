<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhaseCategorie extends Model
{
    use HasFactory;

    protected $table = "phase_categories";


    protected $fillable = [
        'id',
        'tenant_id',
        'template_id',
        'template_category_id',
        'phase_category_name',
        'override_phase_name',
        'phase_category_description',
        'sort_order',
    ];
}
