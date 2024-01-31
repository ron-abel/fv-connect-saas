<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProjectVitalOverrideTitleToTenantCustomVitalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenant_custom_vitals', function (Blueprint $table) {
            $table->string('project_vital_override_title')->after('is_show_project_id')->nullable();
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
