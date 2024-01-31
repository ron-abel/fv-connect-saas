<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id');
            $table->string('Lookup_IP');
            $table->string('Lookup_Name')->nullable();
            $table->string('Lookup_Phone_num')->nullable();
            $table->string('Lookup_Project_Id')->nullable();
            $table->string('Result_Client_Name')->nullable();
            $table->string('Result_Project_Id')->nullable();
            $table->integer('Result')->nullable();
            $table->string('available_contact_numbers')->nullable();
            $table->string('verified_contact_number')->nullable();
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
        Schema::dropIfExists('log');
    }
}
