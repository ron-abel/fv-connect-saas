<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeOfLineToAutoNoteGoogleReviewMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('auto_note_google_review_messages', function (Blueprint $table) {
            $table->enum('type_of_line', ['PhaseChange', 'ReviewRequest'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('auto_note_google_review_messages', function (Blueprint $table) {
            $table->dropColumn('type_of_line');
        });
    }
}
