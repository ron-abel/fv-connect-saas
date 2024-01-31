<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnTargetFieldTypeToClientFileUploadConfigurationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_file_upload_configurations', function (Blueprint $table) {
            $table->string('target_field_type')->after('target_field_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_file_upload_configurations', function (Blueprint $table) {
            //
        });
    }
}
