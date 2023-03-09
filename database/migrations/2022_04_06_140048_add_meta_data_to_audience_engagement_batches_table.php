<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMetaDataToAudienceEngagementBatchesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('audience_engagement_batches', static function (Blueprint $table) {
            $table->json('meta_data')->nullable()->after('size');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('audience_engagement_batches', static function (Blueprint $table) {
            $table->dropColumn('meta_data');
        });
    }
}
