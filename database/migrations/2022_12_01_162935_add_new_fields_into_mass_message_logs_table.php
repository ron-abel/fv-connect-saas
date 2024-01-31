<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsIntoMassMessageLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mass_message_logs', function (Blueprint $table) {
            $table->text('note')->after('sent_at')->nullable();
            $table->dateTime('failed_at')->after('note')->nullable();
            $table->integer('failed_count')->after('failed_at')->default(0);
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
            //
        });
    }
}
