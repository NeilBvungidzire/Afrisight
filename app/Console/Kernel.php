<?php

namespace App\Console;

use App\Jobs\CheckTransactionStatus;
use App\Jobs\RunAudienceEngagement;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            CheckTransactionStatus::dispatch()->delay(1);
        })->hourly();

//        $schedule->call(function () {
//            RunAudienceEngagement::dispatch();
//        })->everyFiveMinutes();

        $schedule->command('horizon:snapshot')->everyFiveMinutes();

        $schedule->command('telescope:prune --hours=72')->daily();

//        $schedule->call(static function() {
//            if ( ! \App\Libraries\Project\AutoAudienceEngagement::canRun()) {
//                return;
//            }
//
//            if ($audienceEngagement = \App\AudienceEngagement::getNext(\App\Libraries\Project\AutoAudienceEngagement::getRequiredColumns())) {
//                (new \App\Libraries\Project\AutoAudienceEngagement($audienceEngagement))->engage();
//            }
//        })->everyFiveMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
