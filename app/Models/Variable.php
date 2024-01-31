<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Variable extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function variable_permission()
    {
        return $this->hasMany(VariablePermission::class);
    }

    public static function getVariableKeyByPage($field_name)
    {
        return DB::table('variables')
            ->select(DB::raw('group_concat(variables.variable_key) as variable_keys'))
            ->leftJoin('variable_permissions', 'variables.id', '=', 'variable_permissions.variable_id')
            ->where('variable_permissions.' . $field_name, true)
            ->first()->variable_keys;
    }

    public static function validateVariable($variable_key, $permission_field_name)
    {
        return DB::table('variables')
            ->join('variable_permissions', 'variables.id', '=', 'variable_permissions.variable_id')
            ->where('variables.variable_key', $variable_key)
            ->where('is_active', true)
            ->where('variable_permissions.' . $permission_field_name, true)
            ->count();
    }

    public static function getVariableByPageKey($page_key, $is_custom_variable = false)
    {
        return DB::table('variables')
            ->join('variable_permissions', 'variables.id', '=', 'variable_permissions.variable_id')
            ->where('is_active', true)
            ->where('variable_permissions.' . $page_key, true)
            ->where('is_custom_variable', $is_custom_variable)
            ->get();
    }
}
