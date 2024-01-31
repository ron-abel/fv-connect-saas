<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsForExpandedWorkflowToAutomatedWorkflowActions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('automated_workflow_actions', function (Blueprint $table) {
            $table->string('fv_project_task_assign_type')->after('fv_project_task_body')->nullable();
            $table->string('fv_project_task_assign_user_role')->after('fv_project_task_assign_user_name')->nullable();
            $table->string('fv_project_task_assign_user_role_name')->after('fv_project_task_assign_user_role')->nullable();
            $table->integer('section_visibility_project_type_id')->after('fv_client_hashtag')->nullable();
            $table->string('section_visibility_section_selector')->after('section_visibility_project_type_id')->nullable();
            $table->string('section_visibility')->after('section_visibility_section_selector')->nullable();
            $table->string('phase_assignment')->after('section_visibility')->nullable();
            $table->integer('phase_assignment_project_type_id')->after('phase_assignment')->nullable();
            $table->string('project_phase_id_native')->after('phase_assignment')->nullable();
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
