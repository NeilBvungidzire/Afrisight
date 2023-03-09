<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTotalCompleteLimitToProjectsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void {
        Schema::table('projects', static function (Blueprint $table) {
            $table->integer('total_complete_limit')
                ->default(0)
                ->after('is_live');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::table('projects', static function (Blueprint $table) {
            $table->dropColumn('total_complete_limit');
        });
    }
}
