<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddNewValueDocumentSharedToColumnPrimaryTriggerToTableAutomatedWorkflowTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('automated_workflow_triggers', function (Blueprint $table) {
            DB::statement("ALTER TABLE automated_workflow_triggers MODIFY primary_trigger ENUM('Project','Contact','Note','CollectionItem','Appointment','Section','ProjectRelation','TeamMessageReply','DocumentUploaded','FormSubmitted','SMSReceived','CalendarFeedback', 'DocumentShared')");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('automated_workflow_triggers', function (Blueprint $table) {
            //
        });
    }
}
