<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInitiatorToBlacklistsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void {
        Schema::table('blacklists', static function (Blueprint $table) {
            $table->string('initiator', 25)
                ->nullable()
                ->after('banned_person_ids');
        });

        DB::table('blacklists')
            ->update([
                'initiator' => \App\Constants\BlacklistInitiator::ADMINISTRATOR,
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::table('blacklists', static function (Blueprint $table) {
            $table->dropColumn('initiator');
        });
    }
}
