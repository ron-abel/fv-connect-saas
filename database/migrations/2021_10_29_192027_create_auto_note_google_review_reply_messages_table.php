<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutoNoteGoogleReviewReplyMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auto_note_google_review_reply_messages', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id');
            $table->integer('client_id')->nullable();
            $table->string('message_id')->nullable();
            $table->text('message_body')->nullable();
            $table->string('from_number')->nullable();
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
        Schema::dropIfExists('auto_note_google_review_reply_messages');
    }
}
