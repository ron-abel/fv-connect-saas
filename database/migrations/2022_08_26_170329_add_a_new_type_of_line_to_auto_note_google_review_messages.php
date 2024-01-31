<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddANewTypeOfLineToAutoNoteGoogleReviewMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `auto_note_google_review_messages` CHANGE `type_of_line` `type_of_line` ENUM('PhaseChange', 'ReviewRequest', '2FAVerification') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
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
