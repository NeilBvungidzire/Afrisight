<?php

use App\Constants\RespondentStatus;
use App\Transaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class GenerateTransactionForRespondents extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $respondents = DB::table('respondents')
            ->where('current_status', RespondentStatus::COMPLETED)
            ->where('incentive_amount', '>', 0)
            ->get(['id', 'person_id', 'incentive_amount']);

        foreach ($respondents as $respondent) {
            Transaction::firstOrCreateRespondentRewarding(
                $respondent->person_id,
                $respondent->id,
                $respondent->incentive_amount
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
