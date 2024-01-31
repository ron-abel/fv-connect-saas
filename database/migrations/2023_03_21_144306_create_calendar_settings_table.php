<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCalendarSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calendar_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id')->nullable();
            $table->boolean('calendar_visibility')->default(false);
            $table->boolean('collect_appointment_feedback')->default(false);
            $table->integer('feedback_type')->nullable();
            $table->integer('sync_feedback_type')->nullable();
            $table->integer('project_type_id')->nullable();
            $table->string('project_type_name')->nullable();
            $table->string('collection_section_id')->nullable();
            $table->string('collection_section_name')->nullable();
            $table->boolean('display_as')->default(false);
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
        Schema::dropIfExists('calendar_settings');
    }
}
