<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebhookSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webhook_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id');
            $table->string('trigger_action_name')->nullable();
            $table->text('filevine_hook_url')->nullable();
            $table->text('delivery_hook_url')->nullable();
            $table->string('phase_change_type')->nullable();
            $table->string('phase_change_event')->nullable();
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
        Schema::dropIfExists('webhook_settings');
    }
}
