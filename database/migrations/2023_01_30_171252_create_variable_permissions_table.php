<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVariablePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('variable_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variable_id');
            $table->boolean('is_project_timeline')->default(false);
            $table->boolean('is_timeline_mapping')->default(false);
            $table->boolean('is_phase_change_sms')->default(false);
            $table->boolean('is_review_request_sms')->default(false);
            $table->boolean('is_client_banner_message')->default(false);
            $table->boolean('is_automated_workflow_action')->default(false);
            $table->boolean('is_mass_text')->default(false);
            $table->timestamps();
        });

        Schema::table('variable_permissions', function ($table) {
            $table->foreign('variable_id')->references('id')->on('variables')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('variable_permissions');
    }
}
