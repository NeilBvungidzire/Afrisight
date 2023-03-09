<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TableMemberProfilingAnswersQueryImprovements extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('member_profiling_answers', static function (Blueprint $table) {
            $table->string('profiling_question_id')->index()->change();
            $table->string('person_id')->index()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('member_profiling_answers', static function (Blueprint $table) {
            $table->dropIndex(['profiling_question_id']);
            $table->dropIndex(['person_id']);
        });
    }
}
