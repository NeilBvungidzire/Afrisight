<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('iso_alpha_2', 2)->unique();
            $table->string('iso_alpha_3', 3)->unique();
            $table->string('name');
            $table->string('currency_code', 3);
            $table->boolean('active')->default(0);
            $table->smallInteger('sort')->default(999);

            $table->timestamps();

            $table->index(['iso_alpha_2','iso_alpha_3']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('countries');
    }
}
