<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantNotificationConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenant_notification_configs', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id');
            $table->string('event_name')->nullable(false);
            $table->string('event_short_code')->nullable(false);
            $table->boolean('is_email_notification')->default(false);
            $table->boolean('is_post_to_filevine')->default(false);
            $table->timestamps();
            $table->unique(['tenant_id', 'event_short_code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tenant_notification_configs');
    }
}
