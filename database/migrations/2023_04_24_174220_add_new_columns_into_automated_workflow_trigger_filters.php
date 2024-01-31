<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsIntoAutomatedWorkflowTriggerFilters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('automated_workflow_trigger_filters', function (Blueprint $table) {
            $table->integer('tenant_form_id')->after('fv_section_toggled_section_name')->nullable();
            $table->string('tenant_form_name')->after('tenant_form_id')->nullable();
            $table->integer('client_file_upload_configuration_id')->after('tenant_form_name')->nullable();
            $table->string('client_file_upload_configuration_name')->after('client_file_upload_configuration_id')->nullable();
            $table->string('sms_line')->after('client_file_upload_configuration_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('automated_workflow_trigger_filters', function (Blueprint $table) {
            //
        });
    }
}
