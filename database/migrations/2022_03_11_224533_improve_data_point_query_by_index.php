<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ImproveDataPointQueryByIndex extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('data_points', static function (Blueprint $table) {
            $table->index(['person_id', 'attribute']);
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
            $table->dropIndex(['person_id', 'attribute']);
        });
    }
}
