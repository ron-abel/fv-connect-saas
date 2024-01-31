<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsIntoFvClientPhonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fv_client_phones', function (Blueprint $table) {
            $table->string('client_phone_state')->after('client_phone')->nullable();
            $table->string('client_phone_timezone')->after('client_phone_state')->nullable();
            $table->timestamp('first_sms_sent_at')->after('client_phone_timezone')->nullable();
            $table->timestamp('auto_communication_stop_at')->after('first_sms_sent_at')->nullable();
            $table->timestamp('auto_communication_start_at')->after('auto_communication_stop_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
