<?php

namespace App\Console\Commands;

use App\Imports\ReferredLeadsImport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class ImportReferredLeads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project001:import {batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import leads into database';

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
        $batch = $this->argument('batch');

        switch ($batch) {
            case 3:
                $this->importBatch3();
                break;
        }
    }

    private function importBatch3()
    {
        Excel::import(new ReferredLeadsImport, '/referral/referrals_01.csv');
        $this->info('Batch: ' . 3);
    }
}
