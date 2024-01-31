<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantNotificationLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenant_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id')->nullable();
            $table->string('event_name')->nullable();
            $table->timestamp('sent_email_notification_at')->nullable();
            $table->timestamp('sent_post_to_filevine_at')->nullable();
            $table->integer('fv_project_id')->nullable();
            $table->string('fv_project_name')->nullable();
            $table->integer('fv_client_id')->nullable();
            $table->string('fv_client_name')->nullable();
            $table->text('notification_body')->nullable();
            $table->text('note')->nullable();
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
        Schema::dropIfExists('tenant_notification_logs');
    }
}
