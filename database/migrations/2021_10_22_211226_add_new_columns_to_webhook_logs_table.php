<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsToWebhookLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('webhook_logs', function (Blueprint $table) {
            $table->renameColumn('phase_change_type', 'item_change_type');
            $table->string('fv_object_id')->nullable()->after('fv_phaseName');
            $table->string('fv_object')->nullable()->after('fv_object_id');
            $table->string('fv_event')->nullable()->after('fv_object');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('webhook_logs', function (Blueprint $table) {
            //
        });
    }
}
