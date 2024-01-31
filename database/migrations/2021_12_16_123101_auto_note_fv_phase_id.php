<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AutoNoteFvPhaseId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('auto_note_phases', function (Blueprint $table) {
            $table->string('fv_phase_id')->nullable()->after('phase_name');
        });
        Schema::table('webhook_settings', function (Blueprint $table) {
            $table->string('fv_phase_id')->nullable()->after('phase_change_event');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('auto_note_phases', function (Blueprint $table) {
            $table->dropColumn('fv_phase_id');
        });
        Schema::table('webhook_settings', function (Blueprint $table) {
            $table->dropColumn('fv_phase_id');
        });
    }
}
