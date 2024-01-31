<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantFormMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenant_form_mappings', function (Blueprint $table) {
            $table->id();
            $table->integer('form_id');
            $table->integer('form_mapping_enable')->default(false);
            $table->string('form_item_name')->nullable();
            $table->string('form_item_type')->nullable();
            $table->string('form_item_label')->nullable();
            $table->string('fv_project_type_id')->nullable();
            $table->string('fv_project_type_name')->nullable();
            $table->string('fv_section_id')->nullable();
            $table->string('fv_section_name')->nullable();
            $table->string('fv_field_id')->nullable();
            $table->string('fv_field_name')->nullable();
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
        Schema::dropIfExists('tenant_form_mappings');
    }
}
