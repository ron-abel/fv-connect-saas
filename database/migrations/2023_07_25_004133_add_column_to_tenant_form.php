<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToTenantForm extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenant_forms', function (Blueprint $table) {
            $table->boolean('sync_existing_fv_project')->after('success_message')->default(false);
            $table->integer('fv_project_id')->after('sync_existing_fv_project')->nullable();
            $table->string('fv_project_name')->after('fv_project_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tenant_forms', function (Blueprint $table) {
            //
        });
    }
}
