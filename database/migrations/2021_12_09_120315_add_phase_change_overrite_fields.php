<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPhaseChangeOverriteFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('phase_categories', function (Blueprint $table) {
            $table->string('override_phase_name')->after('phase_category_name')->nullable();
        });
        Schema::table('phase_mappings', function (Blueprint $table) {
            $table->string('overrite_phase_name')->after('type_phase_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('phase_categories', function (Blueprint $table) {
            $table->dropColumn('override_phase_name');
        });
        Schema::table('phase_mappings', function (Blueprint $table) {
            $table->dropColumn('overrite_phase_name');
        });
    }
}
