<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameAutoNoteGoogleReviewMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('auto_note_google_review_reply_messages','auto_note_google_review_messages');

        Schema::table('auto_note_google_review_messages', function($table) {
            $table->enum('msg_type',['in','out'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
