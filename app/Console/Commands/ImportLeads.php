<?php

namespace App\Console\Commands;

use App\Imports\LeadsImport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class ImportLeads extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leads:import {action}';

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
        $action = $this->argument('action');

        switch ($action) {

            case 1:
                $this->executeAction1();
                break;

            case 2:
                $this->executeAction2();
                break;

            case 3:
                $this->executeAction3();
                break;
        }

        $this->info('Action: ' . $action);
    }

    private function executeAction1()
    {
        Excel::import(new LeadsImport('ethiopia_students_list_2'), 'ethopia_universities_students_list_2.csv');
    }

    private function executeAction2()
    {
        Excel::import(new LeadsImport('ethiopia_students_list_3'), 'ethopia_universities_students_list_3.csv');
    }

    private function executeAction3()
    {
        Excel::import(new LeadsImport('ethiopia_students_list_4'), 'ethopia_universities_students_list_4.csv');
    }
}
