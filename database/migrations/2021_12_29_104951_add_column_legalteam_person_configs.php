<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnLegalteamPersonConfigs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('legalteam_person_configs', function (Blueprint $table) {
        $table->enum('type', ['fetch', 'static'])->after('tenant_id');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('legalteam_person_configs', function($table) {
        $table->dropColumn('type');
      });
    }
}
