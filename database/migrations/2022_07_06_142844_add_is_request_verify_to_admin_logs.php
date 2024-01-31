<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsRequestVerifyToAdminLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_logs', function (Blueprint $table) {
            $table->tinyInteger('is_request_verify')->default(0)->after('is_confirmed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admin_logs', function (Blueprint $table) {
            $table->dropColumn('is_request_verify');
        });
    }
}
