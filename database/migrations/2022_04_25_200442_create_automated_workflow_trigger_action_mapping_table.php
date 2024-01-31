<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomatedWorkflowTriggerActionMappingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automated_workflow_trigger_action_mappings', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id');
            $table->integer('trigger_id');
            $table->integer('action_id');
            $table->enum('status', ['test', 'pause', 'live'])->default('live');
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
        Schema::dropIfExists('automated_workflow_trigger_action_mappings');
    }
}
