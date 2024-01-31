<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFvNoteCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fv_note_comments', function (Blueprint $table) {
            $table->id();
            $table->string('fv_project_id')->nullable();
            $table->string('fv_note_id')->nullable();
            $table->string('fv_comment_id')->nullable();
            $table->string('fv_comment_body')->nullable();
            $table->string('client_name')->nullable();
            $table->string('client_email')->nullable();
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
        Schema::dropIfExists('fv_note_comments');
    }
}
