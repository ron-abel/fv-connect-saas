<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientFileUploadConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_file_upload_configurations', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id');
            $table->integer('project_type_id')->nullable();
            $table->string('project_type_name')->nullable();
            $table->tinyInteger('is_enable_file_uploads')->nullable()->default(0);
            $table->tinyInteger('is_defined_organization_scheme')->nullable()->default(0);
            $table->string('choice');
            $table->integer('handle_files_action')->nullable();
            $table->string('target_section_id')->nullable();
            $table->string('target_section_name')->nullable();
            $table->string('target_field_id')->nullable();
            $table->string('target_field_name')->nullable();
            $table->string('hashtag')->nullable();
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
        Schema::dropIfExists('client_file_upload_configurations');
    }
}
