<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserRolePermission extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_role_permissions')->truncate();
        DB::table('user_role_permissions')->insert([
            [
                'user_role_id' => 2,
                'tenant_admin_page' => "tenant_dashboard",
                'is_allowed' => 1
            ],
            [
                'user_role_id' => 2,
                'tenant_admin_page' => "tenant_profile",
                'is_allowed' => 1
            ],
            [
                'user_role_id' => 2,
                'tenant_admin_page' => "tenant_billing",
                'is_allowed' => 1
            ],
            [
                'user_role_id' => 2,
                'tenant_admin_page' => "tenant_client_config",
                'is_allowed' => 1
            ],
            [
                'user_role_id' => 2,
                'tenant_admin_page' => "tenant_legal_team",
                'is_allowed' => 1
            ],
            [
                'user_role_id' => 2,
                'tenant_admin_page' => "tenant_phase_categories",
                'is_allowed' => 1
            ],
            [
                'user_role_id' => 2,
                'tenant_admin_page' => "tenant_phase_mapping",
                'is_allowed' => 1
            ],
            [
                'user_role_id' => 2,
                'tenant_admin_page' => "tenant_phase_change_sms",
                'is_allowed' => 1
            ],
            [
                'user_role_id' => 2,
                'tenant_admin_page' => "tenant_review_requests",
                'is_allowed' => 1
            ],
            [
                'user_role_id' => 2,
                'tenant_admin_page' => "tenant_webhooks",
                'is_allowed' => 1
            ],
            [
                'user_role_id' => 2,
                'tenant_admin_page' => "tenant_contacts",
                'is_allowed' => 1
            ],
            [
                'user_role_id' => 2,
                'tenant_admin_page' => "tenant_support",
                'is_allowed' => 1
            ],
            [
                'user_role_id' => 2,
                'tenant_admin_page' => "tenant_managers",
                'is_allowed' => 1
            ],
            [
                'user_role_id' => 5,
                'tenant_admin_page' => "tenant_dashboard",
                'is_allowed' => 1
            ],
            [
                'user_role_id' => 5,
                'tenant_admin_page' => "tenant_profile",
                'is_allowed' => 0
            ],
            [
                'user_role_id' => 5,
                'tenant_admin_page' => "tenant_billing",
                'is_allowed' => 0
            ],
            [
                'user_role_id' => 5,
                'tenant_admin_page' => "tenant_client_config",
                'is_allowed' => 0
            ],
            [
                'user_role_id' => 5,
                'tenant_admin_page' => "tenant_legal_team",
                'is_allowed' => 0
            ],
            [
                'user_role_id' => 5,
                'tenant_admin_page' => "tenant_phase_categories",
                'is_allowed' => 0
            ],
            [
                'user_role_id' => 5,
                'tenant_admin_page' => "tenant_phase_mapping",
                'is_allowed' => 0
            ],
            [
                'user_role_id' => 5,
                'tenant_admin_page' => "tenant_phase_change_sms",
                'is_allowed' => 0
            ],
            [
                'user_role_id' => 5,
                'tenant_admin_page' => "tenant_review_requests",
                'is_allowed' => 0
            ],
            [
                'user_role_id' => 5,
                'tenant_admin_page' => "tenant_webhooks",
                'is_allowed' => 0
            ],
            [
                'user_role_id' => 5,
                'tenant_admin_page' => "tenant_contacts",
                'is_allowed' => 0
            ],
            [
                'user_role_id' => 5,
                'tenant_admin_page' => "tenant_support",
                'is_allowed' => 1
            ],
            [
                'user_role_id' => 5,
                'tenant_admin_page' => "tenant_managers",
                'is_allowed' => 0
            ],
            [
                'user_role_id' => 2,
                'tenant_admin_page' => "client_file_upload_config",
                'is_allowed' => 1
            ],
            [
                'user_role_id' => 2,
                'tenant_admin_page' => "forms",
                'is_allowed' => 1
            ],
            [
                'user_role_id' => 2,
                'tenant_admin_page' => "banner_messages",
                'is_allowed' => 1
            ],
        ]);
    }
}
