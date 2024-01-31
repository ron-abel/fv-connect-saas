<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomatedWorkflowTriggerActionMappingRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automated_workflow_trigger_action_mapping_rules', function (Blueprint $table) {
            $table->id();
            $table->string('primary_trigger')->nullable(false);
            $table->string('trigger_event')->nullable(false);
            $table->string('action_name')->nullable(false);
            $table->string('action_short_code')->nullable(false);
            $table->string('status')->default(true);
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
        Schema::dropIfExists('automated_workflow_trigger_action_mapping_rules');
    }
}
