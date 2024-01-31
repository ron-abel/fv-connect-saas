<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFvTeamMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fv_team_members', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id');
            $table->string('fv_user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('first_name', 255)->nullable();
            $table->string('last_name', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('full_name', 255)->nullable();
            $table->string('level')->nullable();
            $table->text('team_org_roles')->nullable();
            $table->text('picture_url')->nullable();
            $table->text('s3_image_url')->nullable();
            $table->boolean('is_primary')->default(0);
            $table->boolean('is_admin')->default(0);
            $table->boolean('is_first_primary')->default(0);
            $table->boolean('is_only_primary')->default(0);
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
        Schema::dropIfExists('fv_team_members');
    }
}