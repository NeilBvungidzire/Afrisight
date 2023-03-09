<?php

namespace App\Console\Commands;

use App\Libraries\Project\ProjectEditor;
use Illuminate\Console\Command;

class CreateProject extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import and create new project into database.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void {
        $projectCode = $this->ask('What\'s the project code?');
        $project = new ProjectEditor($projectCode);
        if ($project->create()) {
            $this->info('Project imported!');
            return;
        }

        $this->info('Somehow the project couldn\'t be imported!');
    }
}
