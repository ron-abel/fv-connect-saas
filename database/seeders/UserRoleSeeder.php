<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserRoleSeeder extends Seeder
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
                'user_role_name' => 'superadmin',
                'user_role_description' => "this is super admin"
            ],
            [
                'id' => 2,
                'user_role_name' => 'tenant-manager',
                'user_role_description' => "this is tenant manager"
            ],
            [
                'id' => 3,
                'user_role_name' => 'tenant-owner',
                'user_role_description' => "this is tenant owner"
            ],
            [
                'id' => 4,
                'user_role_name' => 'tenant-supporter',
                'user_role_description' => "this is tenant supporter"
            ],
        ]);
    }
}
