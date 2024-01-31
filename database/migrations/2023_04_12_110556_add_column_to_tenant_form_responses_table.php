<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToTenantFormResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenant_form_responses', function (Blueprint $table) {
            $table->string('fv_client_name')->after('fv_client_id')->nullable();
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
        Schema::table('tenant_form_responses', function (Blueprint $table) {
            //
        });
    }
}
