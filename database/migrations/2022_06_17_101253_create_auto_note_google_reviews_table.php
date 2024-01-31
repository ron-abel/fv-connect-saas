<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutoNoteGoogleReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auto_note_google_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->boolean('is_set_qualified_response_threshold')->default(false);
            $table->integer('minimum_score')->nullable();
            $table->longText('qualified_review_request_msg_body')->nullable();
            $table->boolean('is_send_unqualified_response_request')->default(false);
            $table->longText('unqualified_review_request_msg_body')->nullable();
            $table->longText('review_request_text_body')->nullable();
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
        Schema::dropIfExists('auto_note_google_reviews');
    }
}
