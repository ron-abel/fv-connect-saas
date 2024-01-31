<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCancelAndExpireToSubscriptionCustomerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_customers', function (Blueprint $table) {
            $table->boolean('is_canceled')->default(0)->after('is_active');
            $table->boolean('is_expired')->default(0)->after('is_canceled');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscription_customers', function (Blueprint $table) {
            //
        });
    }
}
