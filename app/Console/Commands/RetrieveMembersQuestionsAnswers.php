<?php

namespace App\Console\Commands;

use App\Jobs\GetMemberQuestionAnswersFromCint;
use App\Person;
use Illuminate\Console\Command;

class RetrieveMembersQuestionsAnswers extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cint:members:answers {start} {before}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieve members profiling question answers from Cint and save into database.';

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
        $start = $this->argument('start');
        $before = $this->argument('before');

        $delay = now()->addMinutes(5);
        $this->info('Expected start: ' . $delay->format("Y-m-d H:i:s"));
        Person::whereNotNull('country_id')
            ->whereBetween('id', [$start, $before])
            ->chunk(150, function ($persons) use ($delay) {
                foreach ($persons as $person) {
                    GetMemberQuestionAnswersFromCint::dispatch($person)->delay($delay);
                }
                $delay->addMinutes(5);
            });
        $this->info('Expected end: ' . $delay->format("Y-m-d H:i:s"));
    }
}
