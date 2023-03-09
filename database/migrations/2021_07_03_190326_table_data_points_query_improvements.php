<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TableDataPointsQueryImprovements extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('data_points', static function (Blueprint $table) {
            $table->string('person_id')->index()->change();
            $table->string('attribute')->index()->change();
            $table->string('value')->index()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('data_points', static function (Blueprint $table) {
            $table->dropIndex(['person_id']);
            $table->dropIndex(['attribute']);
            $table->dropIndex(['value']);
        });
    }
}
