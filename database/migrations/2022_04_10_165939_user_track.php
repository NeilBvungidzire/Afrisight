<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UserTrack extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void {
        $connection = 'user_tracks';

        Schema::connection($connection)
            ->create('user_tracks', static function (Blueprint $table) {
                $table->timestamp('time');

                $table->ipAddress('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->string('session_key');
                $table->text('uri');
                $table->text('referer')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->json('meta_data')->nullable();
            });

        /**
         * Turn into hypertable.
         *
         * @see https://docs.timescale.com/timescaledb/latest/getting-started/create-hypertable/
         */
        \Illuminate\Support\Facades\DB::connection($connection)
            ->statement("SELECT create_hypertable('user_tracks','time')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        $connection = 'user_tracks';

        Schema::connection($connection)->dropIfExists('user_tracks');
    }
}
