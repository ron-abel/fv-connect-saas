<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateLegalteamConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('legalteam_configs', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('status');
            $table->dropColumn('selection_selector');
            $table->dropColumn('field_selector');
            $table->renameColumn('full_name', 'name');
            $table->renameColumn('phone_number', 'phone');
        });
        Schema::table('legalteam_configs', function (Blueprint $table) {
            $table->integer('fv_role_id')->nullable()->after('tenant_id');
            $table->string('role_title')->after('tenant_id');
            $table->enum('type', ['fetch', 'static'])->after('tenant_id');
            $table->tinyInteger('role_order')->after('phone');
            $table->boolean('is_active')->after('phone');
            $table->boolean('is_enable_email')->after('phone');
            $table->boolean('is_enable_feedback')->after('phone');
            $table->boolean('is_follower_required')->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('legalteam_configs', function (Blueprint $table) {
            $table->dropColumn('type');
        });
        Schema::table('legalteam_configs', function (Blueprint $table) {
            $table->dropColumn('fv_role_id');
            $table->dropColumn('role_title');
            $table->dropColumn('role_order');
            $table->dropColumn('is_active');
            $table->dropColumn('is_enable_email');
            $table->dropColumn('is_enable_feedback');
            $table->dropColumn('is_follower_required');
            $table->string('type')->after('tenant_id');
            $table->renameColumn('name', 'full_name');
            $table->renameColumn('phone', 'phone_number');
        });
    }
}
