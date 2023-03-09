<?php

namespace App\Console\Commands;

use App\Constants\RespondentStatus;
use App\Respondent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SurveyProgress extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'survey:progress';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $inputs = $this->getInput();

        if ( ! $this->confirm('Is this correct?')) {
            $this->info('Aboard!');

            return;
        }

        $this->getLoiInfo($inputs['project_code']);
        $this->getStatusInfo($inputs['project_code']);
    }

    private function getInput()
    {
        $headers = [
            'Parameter',
            'Value',
        ];
        $inputs = [];

        $projectCode = $this->ask('What is the project code?');
        $inputs[] = ['Project Code', $projectCode];

        $this->table($headers, $inputs);

        return [
            'project_code' => $projectCode,
        ];
    }

    private function getLoiInfo(string $projectCode)
    {
        $respondents = Respondent::where('project_code', $projectCode)
            ->where('is_test', false)
            ->where('current_status', RespondentStatus::COMPLETED)
            ->get();

        $events = [];
        $count = $respondents->count();
        $totalMinutes = 0;
        $quickestLoi = null;
        $slowestLoi = null;
        $averageLoi = 0;

        foreach ($respondents as $respondent) {
            $startDate = $respondent->status_history[RespondentStatus::STARTED];
            $endDate = $respondent->status_history[RespondentStatus::COMPLETED];
            $differenceTimestamp = strtotime($endDate) - strtotime($startDate);
            $loi = $differenceTimestamp / 60;

            $events[] = [
                RespondentStatus::STARTED   => $startDate,
                RespondentStatus::COMPLETED => $endDate,
                'LOI'                       => $loi,
            ];

            $totalMinutes += $loi;

            if ($quickestLoi === null) {
                $quickestLoi = $loi;
            }
            if ($slowestLoi === null) {
                $slowestLoi = $loi;
            }

            if ($loi > $slowestLoi) {
                $slowestLoi = $loi;
            }

            if ($loi < $quickestLoi) {
                $quickestLoi = $loi;
            }
        }

        if ($count > 0 && $totalMinutes > 0) {
            $averageLoi = $totalMinutes / $count;
        }

        $this->info('Average LOI: ' . number_format($averageLoi, 0) . ' minutes');
        $this->info('Slowest LOI: ' . number_format($slowestLoi, 0) . ' minutes');
        $this->info('Quickest LOI: ' . number_format($quickestLoi, 0) . ' minutes');
    }

    private function getStatusInfo(string $projectCode)
    {
        $groups = DB::table('respondents')
            ->select('current_status')
            ->addSelect(DB::raw('COUNT(current_status) AS count'))
            ->where('project_code', $projectCode)
            ->groupBy('current_status')
            ->pluck('count', 'current_status');

        $list = [];
        foreach ($groups as $key => $value) {
            $list[] = [
                'key' => $key,
                'value' => $value,
            ];
        }

        $this->table([
            'Status',
            'Count',
        ], $list);
    }
}
