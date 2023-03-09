<?php

namespace App\Console\Commands;

use App\Jobs\SyncCintUser;
use App\Person;
use Illuminate\Console\Command;

class SyncCint extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cint:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync person with Cint account';

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
        $countriesId = [19, 48, 22, 52, 51, 4, 16, 7];

        $count = Person::query()
            ->whereIn('country_id', $countriesId)
            ->count();
        $chunkSize = 10;
        $bar = $this->output->createProgressBar($count / $chunkSize);

        $bar->start();
        $chunk = 0;
        Person::query()
            ->whereIn('country_id', $countriesId)
            ->chunk($chunkSize, function ($persons) use (&$chunk, &$bar) {
                foreach ($persons as $person) {
                    SyncCintUser::dispatch($person, false)->delay($chunk * 5);
                }

                $chunk++;
                $bar->advance();
            });

        $bar->finish();
        $this->info('All is queued to be synced');
    }
}
