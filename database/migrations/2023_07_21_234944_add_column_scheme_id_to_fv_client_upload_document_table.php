<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnSchemeIdToFvClientUploadDocumentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fv_client_upload_documents', function (Blueprint $table) {
            $table->integer('scheme_id')->after('fv_uploader_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fv_client_upload_documents', function (Blueprint $table) {
            //
        });
    }
}
