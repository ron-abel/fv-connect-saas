<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFewColumnToVariablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('variables', function (Blueprint $table) {
            $table->string('fv_field_selector_type')->after('id')->nullable();
            $table->string('fv_field_selector_name')->after('id')->nullable();
            $table->string('fv_field_selector')->after('id')->nullable();
            $table->string('fv_section_selector_name')->after('id')->nullable();
            $table->string('fv_section_selector')->after('id')->nullable();
            $table->string('fv_project_type_name')->after('id')->nullable();
            $table->integer('fv_project_type')->after('id')->nullable();
            $table->boolean('is_custom_variable')->after('id')->default(false);
            $table->integer('tenant_id')->after('id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('variables', function (Blueprint $table) {
            //
        });
    }
}
