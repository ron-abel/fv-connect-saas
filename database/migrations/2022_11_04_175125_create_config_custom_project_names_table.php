<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigCustomProjectNamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config_custom_project_names', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id');
            $table->enum('selected_option', ['client_full_name', 'field_value', 'client_full_name-field_value', 'field_value-field_value'])->nullable();
            $table->integer('fv_project_type_id')->nullable();
            $table->string('fv_project_type_name')->nullable();
            $table->string('fv_section_id')->nullable();
            $table->string('fv_section_name')->nullable();
            $table->string('fv_field_id')->nullable();
            $table->string('fv_field_name')->nullable();
            $table->integer('sec_fv_project_type_id')->nullable();
            $table->string('sec_fv_project_type_name')->nullable();
            $table->string('sec_fv_section_id')->nullable();
            $table->string('sec_fv_section_name')->nullable();
            $table->string('sec_fv_field_id')->nullable();
            $table->string('sec_fv_field_name')->nullable();
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
        Schema::dropIfExists('config_custom_project_names');
    }
}
