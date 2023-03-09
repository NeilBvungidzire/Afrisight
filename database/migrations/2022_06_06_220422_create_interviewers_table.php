<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInterviewersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void {
        Schema::create('interviewers', static function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->string('key');

            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('sample_code')->nullable();

            $table->softDeletes();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::dropIfExists('interviewers');
    }
}
