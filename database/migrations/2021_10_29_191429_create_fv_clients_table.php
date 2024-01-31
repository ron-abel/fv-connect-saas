<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFvClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fv_clients', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id');
            $table->string('fv_client_id')->nullable();
            $table->string('fv_client_name')->nullable();
            $table->string('fv_client_address', 255)->nullable();
            $table->string('fv_client_zip', 255)->nullable();
            $table->boolean('is_google_review_response')->default(0);
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
        Schema::dropIfExists('fv_clients');
    }
}
