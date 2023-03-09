<?php

namespace App\Observers;

use App\Libraries\Payout\Constants\TransactionStatus;
use App\Person;
use App\Services\AccountService\AccountService;
use App\Transaction;

class TransactionObserver {

    /**
     * Handle the transaction "creating" event.
     *
     * @param Transaction $transaction
     *
     * @return void
     */
    public function creating(Transaction $transaction)
    {
        $this->handlePersonBalance($transaction);
    }

    /**
     * Handle the transaction "created" event.
     *
     * @param Transaction $transaction
     *
     * @return void
     */
    public function created(Transaction $transaction)
    {
        //
    }

    /**
     * Handle the transaction "updating" event.
     *
     * @param Transaction $transaction
     *
     * @return void
     */
    public function updating(Transaction $transaction)
    {
        $this->handlePersonBalance($transaction);
    }

    /**
     * Handle the transaction "deleted" event.
     *
     * @param Transaction $transaction
     *
     * @return void
     */
    public function deleted(Transaction $transaction)
    {
        //
    }

    /**
     * Handle the transaction "restored" event.
     *
     * @param Transaction $transaction
     *
     * @return void
     */
    public function restored(Transaction $transaction)
    {
        //
    }

    /**
     * Handle the transaction "force deleted" event.
     *
     * @param Transaction $transaction
     *
     * @return void
     */
    public function forceDeleted(Transaction $transaction)
    {
        //
    }

    /**
     * Recalculate person balance based on transaction state.
     *
     * @param Transaction $transaction
     */
    private function handlePersonBalance(Transaction $transaction): void
    {
        // Adjust person balance when a transaction is approved and person's balance is not yet adjusted accordingly.
        if ( ! $transaction->balance_adjusted && $transaction->status === TransactionStatus::APPROVED) {
            if ( ! $person = Person::withTrashed()->find($transaction->person_id)) {
                return;
            }

            $metaData = $transaction->meta_data;
            $metaData['reward_balance_before'] = $person->reward_balance;
            $person->reward_balance = $person->reward_balance + $transaction->amount;
            $metaData['reward_balance_after'] = $person->reward_balance;
            $transaction->meta_data = $metaData;
            $person->save();
            $transaction->balance_adjusted = true;

            AccountService::clearCachedBalance($person->id);
        }

        // Adjust person balance when a transaction is denied and person's balance was adjusted accordingly.
        if ($transaction->balance_adjusted
            && $transaction->status === TransactionStatus::DENIED
            && $transaction->getOriginal('status') === TransactionStatus::APPROVED
        ) {
            if ( ! $person = Person::withTrashed()->find($transaction->person_id)) {
                return;
            }

            $metaData = $transaction->meta_data;
            $metaData['reward_balance_before'] = $person->reward_balance;
            $person->reward_balance = $person->reward_balance + -($transaction->amount);
            $metaData['reward_balance_after'] = $person->reward_balance;
            $transaction->meta_data = $metaData;

            $person->save();
            $transaction->balance_adjusted = true;

            AccountService::clearCachedBalance($person->id);
        }

        if ($transaction->balance_adjusted
            && $transaction->status === TransactionStatus::APPROVED
            && $transaction->getOriginal('status') === TransactionStatus::DENIED
        ) {
            if ( ! $person = Person::withTrashed()->find($transaction->person_id)) {
                return;
            }

            $metaData = $transaction->meta_data;
            $metaData['reward_balance_before'] = $person->reward_balance;
            $person->reward_balance = $person->reward_balance + $transaction->amount;
            $metaData['reward_balance_after'] = $person->reward_balance;
            $transaction->meta_data = $metaData;
            $person->save();
            $transaction->balance_adjusted = true;

            AccountService::clearCachedBalance($person->id);
        }
    }
}
