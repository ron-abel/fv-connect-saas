<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomatedWorkflowActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automated_workflow_actions', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id')->nullable(false);
            $table->integer('automated_workflow_initial_action_id');
            $table->string('action_name')->nullable();
            $table->string('action_description')->nullable();
            $table->string('note')->nullable();
            $table->boolean('is_active')->default(false);
            $table->string('client_sms_body')->nullable();
            $table->string('fv_project_note_body')->nullable();
            $table->boolean('fv_project_note_with_pin')->default(0);
            $table->string('fv_project_task_body')->nullable();
            $table->string('fv_project_task_assign_user_id')->nullable();
            $table->string('fv_project_task_assign_user_name')->nullable();
            $table->string('email_note_body')->nullable();
            $table->string('fv_project_hashtag')->nullable();
            $table->string('fv_client_hashtag')->nullable();
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
        Schema::dropIfExists('automated_workflow_actions');
    }
}
