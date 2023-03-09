<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberProfilingAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_profiling_answers', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('profiling_question_id');
            $table->unsignedBigInteger('person_id');

            $table->json('answers')->nullable();
            $table->text('other_answer')->nullable();

            $table->softDeletes();
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
        Schema::dropIfExists('member_profiling_answers');
    }
}
