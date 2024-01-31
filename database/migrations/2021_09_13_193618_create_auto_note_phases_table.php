<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutoNotePhasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auto_note_phases', function (Blueprint $table) {
            $table->id();
			$table->integer('tenant_id');
			$table->string('phase_change_type');
			$table->string('phase_name');
			$table->integer('is_active');
			$table->longtext('custom_message')->nullable();
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
        Schema::dropIfExists('auto_note_phases');
    }
}
