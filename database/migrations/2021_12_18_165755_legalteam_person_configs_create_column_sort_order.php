<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LegalteamPersonConfigsCreateColumnSortOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('legalteam_person_configs', function (Blueprint $table) {
            $table->tinyInteger('sort_order')->after('override_email');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('legalteam_person_configs', function (Blueprint $table) {
            //
        });
    }
}
