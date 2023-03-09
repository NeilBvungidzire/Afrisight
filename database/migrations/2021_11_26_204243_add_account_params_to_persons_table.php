<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAccountParamsToPersonsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('persons', static function (Blueprint $table) {
            $table->json('account_params')
                ->after('reward_balance')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('persons', static function (Blueprint $table) {
            $table->dropColumn('account_params');
        });
    }
}
