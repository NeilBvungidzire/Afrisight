<?php

namespace App\Console\Commands;

use App\Constants\RespondentStatus;
use App\Respondent;
use Illuminate\Console\Command;

class SetProjectQuotaToReached extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'survey:quota-reached {projectCode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set all invited respondents current status to quota reached.';

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
        $projectCode = $this->argument('projectCode');

        // Set all to quota full, if limit is realized.
        $invitedRespondents = Respondent::where('current_status', RespondentStatus::INVITED)
            ->where('project_code', $projectCode)
            ->get();
        foreach ($invitedRespondents as $respondent) {
            $respondent->update([
                'current_status' => RespondentStatus::QUOTA_FULL,
                'status_history' => array_merge($respondent->status_history, [
                    RespondentStatus::QUOTA_FULL => date('Y-m-d H:i:s'),
                ]),
            ]);
        }
        $this->info('Number of respondents processed: ' . $invitedRespondents->count());
    }
}
