<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContactChangesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void {
        Schema::create('contact_changes', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('person_id');

            $table->string('contact_reference');
            $table->string('from');
            $table->string('to');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::dropIfExists('contact_changes');
    }
}
