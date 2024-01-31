<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToMassMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mass_messages', function (Blueprint $table) {
            $table->boolean('is_schedule_job')->after('note')->default(0);
            $table->timestamp('schedule_time')->after('is_schedule_job')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mass_messages', function (Blueprint $table) {
            //
        });
    }
}
