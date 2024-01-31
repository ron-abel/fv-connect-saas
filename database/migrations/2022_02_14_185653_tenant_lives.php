<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TenantLives extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenant_lives', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id')->nullable();
            $table->enum('status', array('setup','scheduled','live'))->nullable();
            $table->string('test_tfa_number')->nullable();
            $table->date('scheduled_date')->nullable();
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
        Schema::dropIfExists('tenant_lives');
    }
}
