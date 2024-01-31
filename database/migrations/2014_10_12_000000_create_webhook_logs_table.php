<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebhookLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id');
            $table->string('trigger_action_name')->nullable();
            $table->string('phase_change_event')->nullable();
            $table->string('phase_change_type')->nullable();
            $table->integer('fv_personId')->nullable();
            $table->integer('fv_projectId')->nullable();
            $table->integer('fv_org_id')->nullable();
            $table->integer('fv_userId')->nullable();
            $table->integer('fv_phaseId')->nullable();
            $table->string('fv_phaseName')->nullable();
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
        Schema::dropIfExists('webhook_logs');
    }
}
