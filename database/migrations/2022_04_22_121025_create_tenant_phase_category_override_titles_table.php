<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantPhaseCategoryOverrideTitlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenant_phase_category_override_titles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('template_id');

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
        Schema::dropIfExists('tenant_phase_category_override_titles');
    }
}
