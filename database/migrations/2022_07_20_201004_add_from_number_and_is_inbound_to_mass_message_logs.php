<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFromNumberAndIsInboundToMassMessageLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mass_message_logs', function (Blueprint $table) {
            $table->string('from_number')->nullable();
            $table->boolean('is_inbound')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mass_message_logs', function (Blueprint $table) {
            $table->dropColumn(['from_number', 'is_inbound']);
        });
    }
}
