<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCintTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cint_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Reference ID's
            $table->unsignedBigInteger('person_id');
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
        Schema::dropIfExists('cint_transactions');
    }
}
