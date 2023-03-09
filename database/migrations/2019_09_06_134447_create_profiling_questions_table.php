<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

class CreateProfilingQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profiling_questions', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->uuid('uuid');
            $table->string('title');
            $table->string('type');
            $table->boolean('is_published')->default(false);
            $table->boolean('is_definitive')->default(false);
            $table->json('settings')->nullable();
            $table->json('answer_params')->nullable();
            $table->json('conditions')->nullable();
            $table->smallInteger('sort')->default(999);

            $table->softDeletes();
            $table->timestamps();

            $table->index(['uuid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('profiling_questions');
    }
}
