<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveryHookUrlToAutomatedWorkflowActions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('automated_workflow_actions', function (Blueprint $table) {
            $table->string('delivery_hook_url', 512)->after('project_phase_id_native')->nullable();
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
