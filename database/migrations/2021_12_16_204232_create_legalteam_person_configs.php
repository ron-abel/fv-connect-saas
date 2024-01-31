<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLegalteamPersonConfigs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('legalteam_person_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants');
            $table->string('fv_project_type_id')->nullable();
            $table->string('fv_project_type_name')->nullable();
            $table->string('fv_section_id')->nullable();
            $table->string('fv_section_name')->nullable();
            $table->string('fv_person_field_id')->nullable();
            $table->string('fv_person_field_name')->nullable();
            $table->boolean('is_enable_phone')->default(1);
            $table->boolean('is_enable_email')->default(1);
            $table->boolean('is_enable_feedback')->default(1);
            $table->boolean('is_static_name')->default(0);
            $table->string('override_name')->nullable();
            $table->boolean('is_override_phone')->default(0);
            $table->string('override_phone')->nullable();
            $table->boolean('is_override_email')->default(0);
            $table->string('override_email')->nullable();
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
        Schema::dropIfExists('legalteam_person_configs');
    }
}
