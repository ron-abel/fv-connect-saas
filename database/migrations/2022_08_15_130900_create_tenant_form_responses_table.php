<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantFormResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenant_form_responses', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_form_id');
            $table->integer('fv_client_id');
            $table->integer('fv_project_id');
            $table->text('form_response_values_json')->nullable(false);
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
        Schema::dropIfExists('tenant_form_responses');
    }
}
