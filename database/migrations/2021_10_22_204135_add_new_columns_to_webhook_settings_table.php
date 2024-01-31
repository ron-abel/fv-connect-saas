<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsToWebhookSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('webhook_settings', function (Blueprint $table) {
            $table->renameColumn('phase_change_type', 'item_change_type');
            $table->string('task_changed')->nullable()->after('phase_change_event');
            $table->string('collection_changed')->nullable()->after('task_changed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('webhook_settings', function (Blueprint $table) {
            //
        });
    }
}
