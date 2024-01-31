<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFiledsTenants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('tenants', function (Blueprint $table) {
        $table->string('fv_tenant_base_url')->default('https://app.filevine.com')->after('tenant_law_firm_name');
        $table->string('fv_api_base_url')->default('https://api.filevine.io')->after('fv_tenant_base_url');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('tenants', function (Blueprint $table) {
          //
      });
    }
}
