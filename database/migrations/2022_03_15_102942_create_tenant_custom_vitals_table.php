<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantCustomVitalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenant_custom_vitals', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id');
            $table->string('display_phone_number');
            $table->string('dispaly_email');
            $table->boolean('is_show_project_sms_number')->default(true);
            $table->boolean('is_show_project_email')->default(true);
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
        Schema::dropIfExists('tenant_custom_vitals');
    }
}
