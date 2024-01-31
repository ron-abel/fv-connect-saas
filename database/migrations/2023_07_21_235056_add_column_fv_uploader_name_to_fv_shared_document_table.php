<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnFvUploaderNameToFvSharedDocumentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fv_shared_documents', function (Blueprint $table) {
            $table->string('fv_uploader_name')->after('fv_uploader_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fv_shared_documents', function (Blueprint $table) {
            //
        });
    }
}
