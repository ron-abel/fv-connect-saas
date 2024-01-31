<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMassEmailLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mass_email_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mass_email_id');
            $table->string('person_name');
            $table->string('person_email');
            $table->boolean('is_sent');
            $table->dateTime('sent_at')->nullable();
            $table->text('note')->nullable();
            $table->dateTime('failed_at')->nullable();
            $table->integer('failed_count')->default(0);
            $table->integer('job_id')->nullable();
            $table->foreign('mass_email_id')->references('id')->on('mass_emails')->onDelete('cascade');
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
        Schema::dropIfExists('mass_email_logs');
    }
}
