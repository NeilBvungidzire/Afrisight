<?php

namespace App\Console\Commands;

use App\Jobs\CheckTransactionStatus;
use Illuminate\Console\Command;

class CheckFlutterwaveTransactionStatus extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transaction:check-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Flutterwave transaction status and adjust accordingly.';

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
        CheckTransactionStatus::dispatch();
    }
}
