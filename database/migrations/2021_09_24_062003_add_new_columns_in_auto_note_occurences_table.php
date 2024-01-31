<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsInAutoNoteOccurencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('auto_note_occurrences', function (Blueprint $table) {
            $table->integer('google_review_is_on')->after('is_live');
            $table->integer('google_review_is_live')->after('is_live');
			$table->renameColumn('status', 'is_on');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('auto_note_occurrences', function (Blueprint $table) {
            //
        });
    }
}
