<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserRoleTenantViewer extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_roles')->insert([
            [
                'id' => 1,
                'user_role_name' => 'super_admin',
                'user_role_description' => "this is super admin"
            ],
            [
                'id' => 2,
                'user_role_name' => 'Tenant Manager',
                'user_role_description' => "this is tenant manager"
            ],
            [
                'id' => 3,
                'user_role_name' => 'Tenant Owner',
                'user_role_description' => "this is tenant owner"
            ],
            [
                'id' => 4,
                'user_role_name' => 'Tenant Supporter',
                'user_role_description' => "this is tenant supporter"
            ],
            [
                'id' => 5,
                'user_role_name' => 'Tenant Viewer',
                'user_role_description' => "this is tenant viewer"
            ]
        ]);
    }
}
