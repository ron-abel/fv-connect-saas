<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMassEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mass_emails', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->bigInteger('created_by')->nullable();
            $table->boolean('is_fetch')->default(false);
            $table->boolean('is_upload_csv')->default(false);
            $table->unsignedBigInteger('fv_person_type_id')->nullable();
            $table->string('fv_person_type_name')->nullable();
            $table->boolean('is_exclude_blacklist')->default(false);
            $table->string('upload_csv_file_name')->nullable();
            $table->string('campaign_name')->nullable();
            $table->longText('message_body')->nullable();
            $table->string('note')->nullable();
            $table->boolean('is_schedule_job')->default(0);
            $table->timestamp('schedule_time')->nullable();
            $table->boolean('is_complete')->default(false);
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
        Schema::dropIfExists('mass_emails');
    }
}
