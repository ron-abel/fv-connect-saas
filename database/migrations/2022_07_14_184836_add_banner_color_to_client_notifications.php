<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBannerColorToClientNotifications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_notifications', function (Blueprint $table) {
            $table->enum('banner_color', ['notice', 'warning', 'affirmation', 'calming', 'dark', 'default'])->nullable()->default('default')->after('notice_body');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_notifications', function (Blueprint $table) {
            $table->dropColumn('banner_color');
        });
    }
}
