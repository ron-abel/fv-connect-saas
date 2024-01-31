<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeedbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
			$table->integer('tenant_id');
			$table->integer('project_id')->nullable();
			$table->string('project_name')->nullable();
			$table->string('project_phase')->nullable();
			$table->string('legal_team_name')->nullable();
			$table->string('legal_team_email')->nullable();
			$table->string('legal_team_phone')->nullable();
			$table->string('client_name')->nullable();
			$table->string('client_phone')->nullable();
			$table->string('fd_mark_legal_service')->nullable();
			$table->string('fd_mark_recommend')->nullable();
			$table->string('fd_mark_useful')->nullable();
			$table->longText('fd_content')->nullable();
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
        Schema::dropIfExists('feedbacks');
    }
}
