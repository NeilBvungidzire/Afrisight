<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TableRespondentsQueryImprovements extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('respondents', static function (Blueprint $table) {
            $table->string('person_id')->index()->change();
            $table->string('project_code')->index()->change();
            $table->string('current_status')->index()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('respondents', static function (Blueprint $table) {
            $table->dropIndex(['person_id']);
            $table->dropIndex(['project_code']);
            $table->dropIndex(['current_status']);
        });
    }
}
