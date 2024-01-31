<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomatedWorkflowTriggerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automated_workflow_triggers', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id');
            $table->enum('primary_trigger', ['Project', 'Contact', 'Note', 'CollectionItem', 'Appointment', 'Section', 'ProjectRelation'])->nullable();
            $table->enum('trigger_event', ['Created', 'PhaseChanged', 'AddedHashtag', 'Updated', 'Completed', 'TaskflowButtonTrigger', 'Deleted', 'Visible', 'Hidden', 'Related', 'Unrelated'])->nullable();
            $table->string('trigger_name');
            $table->boolean('is_filter')->default(false);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->unique(['tenant_id', 'trigger_name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('automated_workflow_triggers');
    }
}
