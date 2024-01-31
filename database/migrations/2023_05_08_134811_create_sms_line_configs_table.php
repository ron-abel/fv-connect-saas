<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsLineConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_line_configs', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id')->nullable();
            $table->boolean('post_phase_change_response')->default(false);
            $table->boolean('post_review_request_response')->default(false);
            $table->boolean('post_mass_text_response')->default(false);
            $table->boolean('phase_change_response')->default(false);
            $table->boolean('review_request_response')->default(false);
            $table->boolean('mass_text_response')->default(false);
            $table->text('phase_change_response_text')->nullable();
            $table->text('review_request_response_text')->nullable();
            $table->text('mass_text_response_text')->nullable();
            $table->integer('project_sms_number_order')->default(1);
            $table->integer('mailroom_order')->default(2);
            $table->integer('project_feed_note_order')->default(3);
            $table->string('default_org_mailroom_number')->nullable();
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
        Schema::dropIfExists('sms_line_configs');
    }
}
