<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigProjectVitalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config_project_vitals', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id');
            $table->integer('fv_project_type_id');
            $table->string('fv_project_type');
            $table->string('vital_name');
            $table->string('friendly_name');
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
        Schema::dropIfExists('config_project_vitals');
    }
}
