<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTwofaVerificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twofa_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('service_sid')->nullable();
            $table->string('phone')->nullable();
            $table->string('code')->nullable();
            $table->integer('tries')->default(0);
            $table->integer('status')->default(0);
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
        Schema::dropIfExists('twofa_verifications');
    }
}
