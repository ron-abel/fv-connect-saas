<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToTableAutomatedWorkflowActions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('automated_workflow_actions', function (Blueprint $table) {
            $table->string('send_sms_choice')->nullable();
            $table->integer('person_field_project_type_id')->nullable();
            $table->string('person_field_project_type_name')->nullable();
            $table->string('person_field_project_type_section_selector')->nullable();
            $table->string('person_field_project_type_section_selector_name')->nullable();
            $table->string('person_field_project_type_section_field_selector')->nullable();
            $table->string('person_field_project_type_section_field_selector_name')->nullable();
            $table->integer('mirror_from_field_project_type_id')->nullable();
            $table->string('mirror_from_field_project_type_name')->nullable();
            $table->string('mirror_from_field_project_type_section_selector')->nullable();
            $table->string('mirror_from_field_project_type_section_selector_name')->nullable();
            $table->string('mirror_from_field_project_type_section_field_selector')->nullable();
            $table->string('mirror_from_field_project_type_section_field_selector_name')->nullable();
            $table->integer('mirror_to_field_project_type_id')->nullable();
            $table->string('mirror_to_field_project_type_name')->nullable();
            $table->string('mirror_to_field_project_type_section_selector')->nullable();
            $table->string('mirror_to_field_project_type_section_selector_name')->nullable();
            $table->string('mirror_to_field_project_type_section_field_selector')->nullable();
            $table->string('mirror_to_field_project_type_section_field_selector_name')->nullable();
            $table->string('project_team_choice')->nullable();
            $table->integer('team_member_user_id')->nullable();
            $table->string('team_member_user_name')->nullable();
            $table->string('add_team_member_choice')->nullable();
            $table->string('add_team_member_choice_level')->nullable();
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
