<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOtherRespondentsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void {
        Schema::table('other_respondents', static function (Blueprint $table) {

            $table->dropColumn('sample_id');

            $table->string('interviewer_id')->nullable()->after('source_id');
            $table->mediumInteger('loi')->nullable()->after('meta_data');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::table('other_respondents', static function (Blueprint $table) {

            $table->string('sample_id')->after('sample_code')->nullable();

            $table->dropColumn('interviewer_id');
            $table->dropColumn('loi');

        });
    }
}
