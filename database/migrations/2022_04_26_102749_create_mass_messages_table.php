<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMassMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mass_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->boolean('is_fetch')->default(false);
            $table->boolean('is_upload_csv')->default(false);
            $table->unsignedBigInteger('fv_person_type_id')->nullable();
            $table->string('fv_person_type_name')->nullable();
            $table->boolean('is_exclude_blacklist')->default(false);
            $table->string('upload_csv_file_name')->nullable();
            $table->longText('message_body')->nullable();
            $table->boolean('is_complete')->default(false);

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
        Schema::dropIfExists('mass_messages');
    }
}
