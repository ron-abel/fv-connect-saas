<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAutoNoteGoogleReivewMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('auto_note_google_review_messages', function (Blueprint $table) {
            $table->string('to_number')->nullable()->after('from_number');
            $table->tinyInteger('is_google_review_filter_msg')->default(0)->after('to_number');
            $table->tinyInteger('is_replied')->nullable()->after('is_google_review_filter_msg');
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
