<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariablePermission extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function variable()
    {
        return $this->belongsTo(Variable::class);
    }
}
