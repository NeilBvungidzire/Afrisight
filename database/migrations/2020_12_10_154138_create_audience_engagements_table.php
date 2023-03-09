<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAudienceEngagementsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audience_engagements', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('project_code');
            $table->integer('engagement_limit');
            $table->integer('total_engaged')->default(0);
            $table->integer('batch_size');
            $table->integer('minutes_between_batches');
            $table->dateTime('next_batch_time');
            $table->json('time_windows');
            $table->json('applicable_criteria');
            $table->boolean('is_on')->default(false);
            $table->json('meta_data')->nullable();

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
        Schema::dropIfExists('audience_engagements');
    }
}
