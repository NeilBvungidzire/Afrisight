<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterNextBatchTimeToAudienceEngagementsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('audience_engagements', static function (Blueprint $table) {
            $table->renameColumn('next_batch_time', 'last_batch_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('audience_engagements', static function (Blueprint $table) {
            $table->renameColumn('last_batch_time', 'next_batch_time');
        });
    }
}
