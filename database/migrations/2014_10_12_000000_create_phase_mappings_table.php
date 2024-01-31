<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhaseMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('phase_mappings', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id');
            $table->integer('project_type_id')->nullable();
            $table->string('project_type_name')->nullable();
            $table->integer('type_phase_id');
			$table->string('type_phase_name');
			$table->integer('phase_category_id');
			$table->string('phase_description')->nullable();
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
        Schema::dropIfExists('phase_mappings');
    }
}
