<?php

namespace App\Console\Commands;

use App\Lead;
use Illuminate\Console\Command;

class UpdateLeadsOnJimmaUniversity extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project001:update:leads {action}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update leads data';

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
        $action = $this->argument('action');

        switch ($action) {

            case 1:
                $this->executeAction1();
                break;
        }

        $this->info('Action executed: ' . $action);
    }

    private function executeAction1()
    {
        // Get leads on Jimma University. We know at this moment they are between these ID's, because that is the place
        // on the Excel sheet.
        $leads = Lead::withTrashed()->where('id', '>=', 459)->where('id', '<=', 626)->get();

        $totalProcessed = 0;
        foreach ($leads as $lead) {
            $lead->update([
                'meta_data' => array_merge($lead['meta_data'], [
                    'university' => 'Jimma University',
                ]),
            ]);

            $totalProcessed++;
        }

        $this->info('Total found: ' . $leads->count());
        $this->info('Total processed: ' . $totalProcessed);
    }
}
