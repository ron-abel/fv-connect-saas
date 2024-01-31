<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomatedWorkflowFvSubscriptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automated_workflow_fv_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id');
            $table->string('fv_subscription_id')->nullable();
            $table->string('fv_subscription_link')->nullable();
            $table->string('fv_subscription_event')->nullable();
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
        Schema::dropIfExists('automated_workflow_fv_subscription');
    }
}
