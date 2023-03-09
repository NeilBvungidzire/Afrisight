<?php

namespace App\Console\Commands;

use App\Libraries\Project\ProjectEditor;
use App\Libraries\Project\ProjectUtils;
use Illuminate\Console\Command;

class RefreshProject extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh project cache so new changes will be reflected.';

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
        if (ProjectUtils::getConfigs($projectCode, true)) {
            $this->info('Project refreshed successfully!');
            return;
        }

        $this->info('Somehow the project could not be refreshed!');
    }
}
