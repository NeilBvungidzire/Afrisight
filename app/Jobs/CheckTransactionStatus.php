<?php

namespace App\Jobs;

use App\Constants\TransactionType;
use App\Libraries\Flutterwave\Constants\TransferStatus;
use App\Libraries\Flutterwave\Flutterwave;
use App\Libraries\Payout\Constants\TransactionStatus;
use App\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckTransactionStatus implements ShouldQueue {

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
        $transferStatusMapping = [
            TransferStatus::SUCCESSFUL => TransactionStatus::APPROVED,
            TransferStatus::FAILED     => TransactionStatus::DENIED,
        ];

        $payoutTransactions = Transaction::query()
            ->where('type', TransactionType::REWARD_PAYOUT)
            ->whereJsonContains('meta_data->provider', 'FLUTTERWAVE')
            ->where('status', TransferStatus::PENDING)
            ->where('balance_adjusted', false)
            ->get();

        if ($payoutTransactions->count() === 0) {
            return;
        }

        foreach ($payoutTransactions as $transaction) {
            if ( ! isset($transaction->meta_data['transfer_id'])) {
                continue;
            }

            $transfer = (new Flutterwave())->transfers()->getTransfer($transaction->meta_data['transfer_id']);

            if ( ! isset($transfer['status']) || ! isset($transferStatusMapping[$transfer['status']])) {
                continue;
            }

            $currentStatus = $transferStatusMapping[$transfer['status']];
            if ($transaction->status === $currentStatus) {
                continue;
            }

            $transaction->update([
                'new_status' => $currentStatus,
            ]);
        }
    }
}
