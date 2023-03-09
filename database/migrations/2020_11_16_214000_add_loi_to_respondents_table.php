<?php

use App\Constants\RespondentStatus;
use App\Respondent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Schema;

class AddLoiToRespondentsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('respondents', function (Blueprint $table) {
            $table->unsignedSmallInteger('actual_loi')
                ->nullable()
                ->after('incentive_amount');
        });

        Respondent::query()
            ->where('current_status', RespondentStatus::COMPLETED)
            ->each(function (Respondent $respondent) {

                if ( ! isset($respondent->status_history[RespondentStatus::STARTED])) {
                    return null;
                }

                if ( ! isset($respondent->status_history[RespondentStatus::COMPLETED])) {
                    return null;
                }

                $startTime = Date::createFromFormat('Y-m-d H:i:s',
                    $respondent->status_history[RespondentStatus::STARTED]);
                $endTime = Date::createFromFormat('Y-m-d H:i:s',
                    $respondent->status_history[RespondentStatus::COMPLETED]);

                $loi = $startTime->diffInMinutes($endTime, false);

                $respondent->update([
                    'actual_loi' => $loi,
                ]);

            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('respondents', function (Blueprint $table) {
            $table->dropColumn('actual_loi');
        });
    }
}
