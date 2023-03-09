<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRewardBalanceToCintUsersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cint_users', function (Blueprint $table) {
            $table->float('reward_balance', 6, 2)
                ->default(0)
                ->after('cint_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cint_users', function (Blueprint $table) {
            $table->dropColumn('reward_balance');
        });
    }
}
