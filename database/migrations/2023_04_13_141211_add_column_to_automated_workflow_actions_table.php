<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToAutomatedWorkflowActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('automated_workflow_actions', function (Blueprint $table) {
            $table->string('phase_assignment_project_type_name')->after('phase_assignment_project_type_id')->nullable();
            $table->string('project_phase_id_native_name')->after('project_phase_id_native')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('automated_workflow_actions', function (Blueprint $table) {
            //
        });
    }
}
