<?php

namespace App\Console\Commands;

use App\Cint\ReadQuestionsFile;
use App\CintQuestion;
use Illuminate\Console\Command;

class ExtractCintQuestions extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cint:questions:extract {path} {countryId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extract Cint questions from XML file and save into database.';

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
        $path = $this->argument('path');
        $countryId = $this->argument('countryId');

        if (empty($path)) {
            $this->error('Please set a file path');
        }
        if (empty($countryId)) {
            $this->error('Please set a country ID');
        }

        $data = (new ReadQuestionsFile($path))->getArray();
        CintQuestion::create([
            'country_id' => $countryId,
            'file'       => $path,
            'data'       => $data,
        ]);

        $this->info('File processed');
    }
}
