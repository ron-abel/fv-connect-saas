<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameCityNameToZipCodeColumnInAutoNoteGoogleReviewCitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('auto_note_google_review_cities', function (Blueprint $table) {
            $table->renameColumn('city_name', 'zip_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('auto_note_google_review_cities', function (Blueprint $table) {
            //
        });
    }
}
