<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOtherRespondentsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('other_respondents', static function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->uuid('uuid');
            $table->string('external_id');

            $table->string('source_id');
            $table->string('status');
            $table->json('status_history');
            $table->json('meta_data')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('other_respondents');
    }
}
