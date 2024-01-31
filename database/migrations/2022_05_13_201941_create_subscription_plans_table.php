<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
			$table->string('stripe_plan_id');
			$table->string('stripe_product_id');
			$table->string('plan_name')->nullable();
			$table->string('plan_description')->nullable();
			$table->string('plan_price')->nullable();
			$table->string('plan_interval')->nullable();
			$table->integer('plan_trial_days')->nullable();
			$table->boolean('plan_is_active')->default(1);
			$table->boolean('plan_is_default')->default(1);
			$table->integer('plan_tenant_id')->nullable();
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
        Schema::dropIfExists('subscription_plans');
    }
}
