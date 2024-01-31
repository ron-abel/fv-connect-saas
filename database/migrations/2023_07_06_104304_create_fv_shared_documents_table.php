<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFvSharedDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fv_shared_documents', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id')->nullable();
            $table->integer('fv_document_id')->nullable();
            $table->string('fv_filename')->nullable();
            $table->string('doc_size')->nullable();
            $table->integer('fv_folder_id')->nullable();
            $table->string('fv_folder_name')->nullable();
            $table->integer('fv_project_id')->nullable();
            $table->integer('fv_uploader_id')->nullable();
            $table->timestamp('fv_upload_date')->nullable();
            $table->string('fv_download_url', 512)->nullable();
            $table->string('hash_tag')->nullable();
            $table->integer('download_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fv_shared_documents');
    }
}
