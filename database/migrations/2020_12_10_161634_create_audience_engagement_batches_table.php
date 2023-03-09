<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAudienceEngagementBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audience_engagement_batches', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('audience_engagement_id')->nullable();

            $table->string('code');
            $table->integer('size');

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
        Schema::dropIfExists('audience_engagement_batches');
    }
}
