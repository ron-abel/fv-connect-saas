<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStripeCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stripe_coupons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('stripe_coupon_id');
            $table->string('stripe_coupon_name');
            $table->double('stripe_coupon_percent_off', 10, 2)->nullable();
            $table->double('stripe_coupon_amount')->nullable();
            $table->string('stripe_coupon_currency')->nullable();
            $table->string('stripe_coupon_duration')->nullable();
            $table->integer('stripe_coupon_duration_in_months')->nullable();
            $table->tinyInteger('stripe_coupon_livemode')->nullable()->default(0);
            $table->tinyInteger('stripe_coupon_valid')->nullable()->default(0);
            $table->softDeletes('deleted_at');
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
        Schema::dropIfExists('stripe_coupons');
    }
}
