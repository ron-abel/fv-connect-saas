<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProjectCreateFieldToTenantFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenant_forms', function (Blueprint $table) {
            $table->boolean('is_public_form')->after('is_active')->default(false);
            $table->boolean('create_fv_project')->after('is_public_form')->default(false);
            $table->integer('fv_project_type_id')->after('create_fv_project')->nullable();
            $table->string('fv_project_type_name')->after('fv_project_type_id')->nullable();
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
