<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlacklistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blacklists', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id', false, true);
            $table->string('fv_full_name');
            $table->integer('fv_client_id', false, true);
            $table->string('fv_project_id')->nullable();
            $table->tinyInteger('is_allow_client_potal')->default(0);
            $table->tinyInteger('is_allow_notification')->default(0);
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
        Schema::dropIfExists('blacklists');
    }
}
