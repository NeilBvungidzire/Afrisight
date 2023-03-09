<?php

use App\Constants\RespondentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddIncentiveAmountToRespondentsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('respondents', function (Blueprint $table) {
            $table->float('incentive_amount')
                ->default(0)
                ->after('current_status');
        });

        $respondents = DB::table('respondents')
            ->where('current_status', RespondentStatus::COMPLETED)
            ->whereNotNull('meta_data')
            ->get(['id', 'meta_data']);

        foreach ($respondents as $respondent) {
            $incentiveAmount = 0;
            $metaData = json_decode($respondent->meta_data, true);

            if (isset($metaData['incentive'])) {
                $incentiveAmount = (float)$metaData['incentive'];
            } elseif (isset($metaData['usd_amount'])) {
                $incentiveAmount = (float)$metaData['usd_amount'];
            }

            DB::table('respondents')
                ->where('id', $respondent->id)
                ->update(['incentive_amount' => $incentiveAmount]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('respondents', function (Blueprint $table) {
            $table->dropColumn('incentive_amount');
        });
    }
}
