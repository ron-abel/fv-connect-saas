<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnMirrorFieldProjectTypeSectionFieldSelectorTypeToAutomatedWorkflowActions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('automated_workflow_actions', function (Blueprint $table) {
            $table->string('mirror_from_field_project_type_section_field_selector_type')->after('mirror_from_field_project_type_section_field_selector_name')->nullable();
            $table->string('mirror_to_field_project_type_section_field_selector_type')->after('mirror_to_field_project_type_section_field_selector_name')->nullable();
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
