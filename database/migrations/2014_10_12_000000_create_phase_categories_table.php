<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhaseCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('phase_categories', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id');
            $table->integer('template_id');
            $table->integer('template_category_id');
            $table->string('phase_category_name');
            $table->string('phase_category_description')->nullable();
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
        Schema::dropIfExists('phase_categories');
    }
}
