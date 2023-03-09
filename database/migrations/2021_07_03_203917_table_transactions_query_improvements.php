<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TableTransactionsQueryImprovements extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('transactions', static function (Blueprint $table) {
            $table->index(['person_id']);
            $table->index(['type']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('transactions', static function (Blueprint $table) {
            $table->dropIndex(['person_id']);
            $table->dropIndex(['type']);
            $table->dropIndex(['status']);
        });
    }
}
