<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCalendarSettingSectionFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calendar_setting_section_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('calendar_setting_id')->nullable();
            $table->string('field_id')->nullable();
            $table->string('field_name')->nullable();
            $table->string('field_type')->nullable();
            $table->string('collection_item_id')->nullable();
            $table->string('override_label')->nullable();
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
        Schema::dropIfExists('calendar_setting_section_fields');
    }
}
