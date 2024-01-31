<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBillingShippingColumnsToSubscriptionCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_customers', function (Blueprint $table) {
            $table->string('address')->nullable()->after('trial_ends_at');
            $table->string('description')->nullable()->after('address');
			$table->string('phone')->nullable()->after('description');
			$table->string('shipping')->nullable()->after('phone');
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
