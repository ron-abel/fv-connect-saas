<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClientnameProjectnameProjectidToggleToTenantCustomVitalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenant_custom_vitals', function (Blueprint $table) {
            $table->boolean('is_show_project_clientname')->default(true)->after('is_show_project_email');
            $table->boolean('is_show_project_name')->default(true)->after('is_show_project_clientname');
            $table->boolean('is_show_project_id')->default(true)->after('is_show_project_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tenant_custom_vitals', function (Blueprint $table) {
            //
        });
    }
}
