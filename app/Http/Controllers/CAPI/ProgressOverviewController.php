<?php

namespace App\Http\Controllers\CAPI;

use App\Constants\RespondentStatus;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ProgressOverviewController extends Controller {

    public function project(string $projectCode) {
        if ( ! $config = config("capi.${projectCode}")) {
            return 'project does not exist';
        }

        $status = $config['live'] ? 'LIVE' : 'TEST';

        $generalStatsTest = DB::table('other_respondents')
            ->selectRaw('COUNT(*) as total_completes, AVG(loi) as average_loi')
            ->where('sample_code', $projectCode)
            ->where('status', RespondentStatus::COMPLETED)
            ->where('meta_data->is_test', true)
            ->get()->first();


        $interviewerStatsTest = DB::table('other_respondents')
            ->selectRaw('interviewer_id, status, COUNT(*) as count, AVG(loi) as average_loi')
            ->where('sample_code', $projectCode)
            ->where('meta_data->is_test', true)
            ->whereIn('status', [
                RespondentStatus::STARTED,
                RespondentStatus::COMPLETED,
                RespondentStatus::DISQUALIFIED,
                RespondentStatus::SCREEN_OUT,
                RespondentStatus::QUOTA_FULL,
            ])
            ->groupBy(['interviewer_id', 'status'])
            ->get()->groupBy('interviewer_id');

        $generalStatsLive = DB::table('other_respondents')
            ->selectRaw('COUNT(*) as total_completes, AVG(loi) as average_loi')
            ->where('sample_code', $projectCode)
            ->where('status', RespondentStatus::COMPLETED)
            ->where('meta_data->is_test', null)
            ->get()->first();


        $interviewerStatsLive = DB::table('other_respondents')
            ->selectRaw('interviewer_id, status, COUNT(*) as count, AVG(loi) as average_loi')
            ->where('sample_code', $projectCode)
            ->where('meta_data->is_test', null)
            ->whereIn('status', [
                RespondentStatus::STARTED,
                RespondentStatus::COMPLETED,
                RespondentStatus::DISQUALIFIED,
                RespondentStatus::SCREEN_OUT,
                RespondentStatus::QUOTA_FULL,
            ])
            ->groupBy(['interviewer_id', 'status'])
            ->get()->groupBy('interviewer_id');

        $unknownInterviewerTest = $interviewerStatsTest->pull("");
        $unknownInterviewerLive = $interviewerStatsLive->pull("");

        return view('capi.overview', compact('projectCode', 'status', 'generalStatsTest',
            'interviewerStatsTest', 'unknownInterviewerTest', 'generalStatsLive', 'interviewerStatsLive',
            'unknownInterviewerLive'));
    }
}
