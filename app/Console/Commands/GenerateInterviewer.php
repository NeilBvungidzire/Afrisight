<?php

namespace App\Console\Commands;

use App\Interviewer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;

class GenerateInterviewer extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'capi:interviewer:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add interviewer to table with unique ID.';

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
     * @return mixed
     */
    public function handle() {
        $size = $this->ask('How many interviewers do you want to add?', 1);
        $sampleCode = $this->ask('What\'s the sample code?');

        $data = [];
        for ($i = 0; $i < $size; $i++) {
            $data[] = [
                'key'         => uniqid('', true),
                'sample_code' => $sampleCode,
                'created_at'  => Date::now(),
                'updated_at'  => Date::now(),
            ];
        }

        if (Interviewer::query()->insert($data)) {
            $this->info('Interviewer(s) created');

            foreach ($data as $interviewer) {
                $this->info(route('capi.fieldwork.entry', ['int_id' => $interviewer['key']]));
            }
            return;
        }

        $this->error('Could not create interviewer(s)!');
    }
}
