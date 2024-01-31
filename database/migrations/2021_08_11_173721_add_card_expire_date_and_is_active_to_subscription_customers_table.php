<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCardExpireDateAndIsActiveToSubscriptionCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_customers', function (Blueprint $table) {
            $table->string('card_expire_date')->nullable()->after('shipping');
            $table->boolean('is_active')->default(1)->after('card_expire_date');
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
