<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientAuthFailedSubmitLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_auth_failed_submit_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id')->nullable();
            $table->string('lookup_first_name')->nullable();
            $table->string('lookup_last_name')->nullable();
            $table->string('lookup_phone')->nullable();
            $table->string('lookup_email')->nullable();
            $table->string('client_ip')->nullable();
            $table->boolean('is_handled')->default(0);
            $table->string('handled_action')->nullable();
            $table->timestamp('at_sent_client_note')->nullable();
            $table->timestamp('at_added_black_list')->nullable();
            $table->text('matched_client_info')->nullable();
            $table->string('note')->nullable();
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
        Schema::dropIfExists('client_auth_failed_submit_logs');
    }
}
