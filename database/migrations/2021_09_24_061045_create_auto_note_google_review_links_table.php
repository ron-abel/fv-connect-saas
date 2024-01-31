<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutoNoteGoogleReviewLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auto_note_google_review_links', function (Blueprint $table) {
            $table->id();
			$table->integer('tenant_id');
			$table->string('review_link', 255)->nullable();
			$table->string('handle_type')->nullable();
            $table->integer('is_default')->default(0);
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
        Schema::dropIfExists('auto_note_google_review_links');
    }
}
