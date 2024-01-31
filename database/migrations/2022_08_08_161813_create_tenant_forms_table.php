<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenant_forms', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id');
            $table->string('form_url')->nullable(false);
            $table->string('form_name')->nullable(false);
            $table->text('form_description')->nullable();
            $table->text('form_fields_json')->nullable(false);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tenant_forms');
    }
}
