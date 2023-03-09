<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTargetTracksTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('target_tracks', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('quota_amount');
            $table->float('quota_percentage', 12, 11);
            $table->integer('count')->default(0);
            $table->string('relation');

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
        Schema::dropIfExists('target_tracks');
    }
}
