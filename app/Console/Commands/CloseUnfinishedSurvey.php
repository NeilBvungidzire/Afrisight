<?php

namespace App\Console\Commands;

use App\Constants\RespondentStatus;
use App\Respondent;
use Illuminate\Console\Command;

class CloseUnfinishedSurvey extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'respondent:close';

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
        $inputs = $this->getInputs();
        $projectCode = $inputs['project_code'];
        $minutesFromNow = $inputs['minutes_from_now'];
        $reselectRespondents = $inputs['reselect'];

        $this->info('Project Code: ' . $projectCode);
        $this->info('Minutes from now: ' . $minutesFromNow);
        $this->info('Reselect respondents: ' . ($reselectRespondents ? 'Yes' : 'No'));
        if ( ! $this->confirm('Is this correct?')) {
            $this->info('Abandoned!');

            return;
        }

        $this->processRespondents($projectCode, $minutesFromNow, $reselectRespondents);
    }

    private function getInputs()
    {
        $projectCode = $this->ask('What is the project code?');
        $minutesFromNow = $this->ask('How many minutes from now?');
        $reselectRespondents = $this->confirm('Do you want to reselect these respondents?');

        return [
            'project_code'     => $projectCode,
            'minutes_from_now' => $minutesFromNow,
            'reselect'         => $reselectRespondents,
        ];
    }

    private function processRespondents(string $projectCode, int $minutesFromNow, bool $reselectRespondents = false)
    {
        $maxDate = date('Y-m-d H:i:s', strtotime('-' . $minutesFromNow . ' minutes'));

        $query = Respondent::query();
        $query
            ->where('project_code', $projectCode)
            ->where('current_status', RespondentStatus::STARTED)
            ->where('updated_at', '<', $maxDate);

        if ($reselectRespondents) {
            $query->with('person');
        }

        $respondents = $query->get();

        foreach ($respondents as $respondent) {
            $respondent->update([
                'current_status' => RespondentStatus::CLOSED,
                'status_history' => array_merge($respondent->status_history, [
                    RespondentStatus::CLOSED => date('Y-m-d H:i:s'),
                ]),
            ]);

            if ($reselectRespondents && $respondent->person) {
                $this->reselectRespondent($respondent->person_id, $projectCode);
            }
        }
    }

    private function reselectRespondent(int $personId, string $projectCode)
    {
        Respondent::create([
            'person_id'      => $personId,
            'project_code'   => $projectCode,
            'current_status' => RespondentStatus::SELECTED,
            'status_history' => [
                RespondentStatus::SELECTED => date('Y-m-d H:i:s'),
            ],
        ]);
    }
}
