<?php

namespace App\Jobs;

use App\Libraries\Payout\Constants\TransactionStatus;
use App\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UndoCintTransaction implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $transactionsQuery = Transaction::query();
        $transactionsQuery->limit(5);
        $transactionsQuery->where('status', TransactionStatus::APPROVED);
        $transactionsQuery->whereJsonContains('meta_data->external_party', 'CINT');
        $transactions = $transactionsQuery->get();

        foreach ($transactions as $transaction) {
            $transaction->update([
                'new_status' => TransactionStatus::DENIED,
            ]);
        }
    }
}
