<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMassMessageLogsTable extends Migration
{
    /**
     * Run the migrations.
     *id, mass_message_id, person_name, person_number,

    is_sent : default 0,
     * @return void
     */
    public function up()
    {
        Schema::create('mass_message_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mass_message_id');
            $table->string('person_name');
            $table->string('person_number');
            $table->boolean('is_sent');
            $table->dateTime('sent_at')->nullable();

            $table->foreign('mass_message_id')->references('id')->on('mass_messages')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mass_message_logs');
    }
}
