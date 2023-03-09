<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDatapointIdentifierToProfilingQuestionsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('profiling_questions', static function (Blueprint $table) {
            $table->string('datapoint_identifier')
                ->nullable()
                ->after('conditions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('profiling_questions', static function (Blueprint $table) {
            $table->dropColumn('datapoint_identifier');
        });
    }
}
