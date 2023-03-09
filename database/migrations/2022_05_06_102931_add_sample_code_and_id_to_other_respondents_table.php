<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSampleCodeAndIdToOtherRespondentsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('other_respondents', static function (Blueprint $table) {
            $table->string('sample_code')->after('uuid')->nullable();
            $table->string('sample_id')->after('sample_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('other_respondents', static function (Blueprint $table) {
            $table->dropColumn('sample_code');
            $table->dropColumn('sample_id');
        });
    }
}
