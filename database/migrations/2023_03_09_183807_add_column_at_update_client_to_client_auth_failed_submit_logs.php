<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnAtUpdateClientToClientAuthFailedSubmitLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_auth_failed_submit_logs', function (Blueprint $table) {
            $table->timestamp('at_update_client')->after('at_added_black_list')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_auth_failed_submit_logs', function (Blueprint $table) {
            //
        });
    }
}
