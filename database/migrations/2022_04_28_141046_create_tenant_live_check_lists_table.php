<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantLiveCheckListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenant_live_check_lists', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id')->nullable();
            $table->tinyInteger('is_client_portal_branded')->default(0);
            $table->tinyInteger('is_api_key')->default(0);
            $table->tinyInteger('is_custom_vitals')->default(0);
            $table->tinyInteger('is_legal_team')->default(0);
            $table->tinyInteger('is_case_status_mapping')->default(0);
            $table->tinyInteger('is_sms_review_request')->default(0);
            $table->tinyInteger('is_test_case_reviewed')->default(0);
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
        Schema::dropIfExists('tenant_live_check_lists');
    }
}
