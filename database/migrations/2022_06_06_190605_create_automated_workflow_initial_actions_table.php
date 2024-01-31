<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomatedWorkflowInitialActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automated_workflow_initial_actions', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id')->nullable(false);
            $table->string('action_short_code')->nullable(false);
            $table->string('action_name')->nullable(false);
            $table->string('action_description')->nullable();
            $table->boolean('is_active')->default(1);
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
        Schema::dropIfExists('automated_workflow_initial_actions');
    }
}
