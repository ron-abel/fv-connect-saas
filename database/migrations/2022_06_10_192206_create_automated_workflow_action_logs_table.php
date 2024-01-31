<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomatedWorkflowActionLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automated_workflow_action_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id')->nullable(false);
            $table->integer('automated_workflow_log_id')->nullable(false);
            $table->integer('trigger_id');
            $table->integer('action_id');
            $table->integer('fv_project_id');
            $table->integer('fv_client_id');
            $table->string('emails');
            $table->string('sms_phones');
            $table->string('note_body');
            $table->boolean('is_handled')->default(false);
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
        Schema::dropIfExists('automated_workflow_action_logs');
    }
}
