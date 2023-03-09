<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRespondentInvitationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('respondent_invitations', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->uuid('uuid');
            $table->unsignedBigInteger('respondent_id');

            $table->string('type');
            $table->string('status');

            $table->timestamps();

            $table->index('uuid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('respondent_invitations');
    }
}
