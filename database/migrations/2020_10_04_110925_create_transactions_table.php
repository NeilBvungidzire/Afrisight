<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Reference ID's
            $table->unsignedBigInteger('person_id');
            $table->uuid('uuid')->nullable();

            $table->string('type');
            $table->string('initiator');
            $table->float('amount');
            $table->string('status');
            $table->boolean('balance_adjusted');
            $table->json('status_history');
            $table->json('meta_data')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
