<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomatedWorkflowTriggerFilterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automated_workflow_trigger_filters', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id');
            $table->integer('trigger_id');
            $table->integer('fv_project_type_id')->nullable();
            $table->string('fv_project_type_name')->nullable();
            $table->integer('fv_project_phase_id')->nullable();
            $table->string('fv_project_phase_name')->nullable();
            $table->string('fv_project_hashtag')->nullable();
            $table->enum('fv_contact_filter_type_name',['Person Types','Contact Hashtags'])->nullable();
            $table->integer('fv_contact_person_type_id')->nullable();
            $table->string('fv_contact_person_type_name')->nullable();
            $table->string('fv_contact_hashtag')->nullable();
            $table->enum('fv_task_filter_type_name',['Task Hashtags','Assigned To','Created By','Auto-Generated Task','Taskflow','Completed By'])->nullable();
            $table->string('fv_task_hashtag')->nullable();
            $table->integer('fv_task_assigned_user_id')->nullable();
            $table->string('fv_task_assigned_user_name')->nullable();
            $table->integer('fv_task_created_user_id')->nullable();
            $table->string('fv_task_created_user_name')->nullable();
            $table->integer('fv_task_completed_user_id')->nullable();
            $table->string('fv_task_completed_user_name')->nullable();
            $table->integer('fv_taskflow_project_type_id')->nullable();
            $table->string('fv_taskflow_project_type_name')->nullable();
            $table->string('fv_taskflow_section_id')->nullable();
            $table->string('fv_taskflow_section_name')->nullable();
            $table->string('fv_taskflow_field_id')->nullable();
            $table->string('fv_taskflow_field_name')->nullable();
            $table->integer('fv_collection_item_project_type_id')->nullable();
            $table->string('fv_collection_item_project_type_name')->nullable();
            $table->string('fv_collection_item_section_id')->nullable();
            $table->string('fv_collection_item_section_name')->nullable();
            $table->string('fv_collection_item_field_id')->nullable();
            $table->string('fv_collection_item_field_name')->nullable();
            $table->enum('fv_calendar_filter_type_name',['Note Hashtag','All Day Appointment','Attendee'])->nullable();
            $table->string('fv_calendar_hashtag')->nullable();
            $table->integer('fv_calendar_attendee_user_id')->nullable();
            $table->string('fv_calendar_attendee_user_name')->nullable();
            $table->integer('fv_section_toggled_project_type_id')->nullable();
            $table->string('fv_section_toggled_project_type_name')->nullable();
            $table->string('fv_section_toggled_section_id')->nullable();
            $table->string('fv_section_toggled_section_name')->nullable();
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
        Schema::dropIfExists('automated_workflow_trigger_filter');
    }
}
