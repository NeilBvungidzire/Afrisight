<?php

namespace App\Http\Controllers\Admin\RewardManagement;

use App\Alert\Facades\Alert;
use App\Constants\TransactionType;
use App\Http\Controllers\Controller;
use App\Jobs\ImportTransaction;
use App\Jobs\SyncCintUser;
use App\Libraries\Payout\Constants\TransactionInitiator;
use App\Libraries\Payout\Constants\TransactionStatus;
use App\Person;

class ImportTransactionsController extends Controller {

    public function importTransactions()
    {
        $this->authorize('reward-management');

        $statuses = TransactionStatus::getConstants();
        $defaultStatus = TransactionStatus::CREATED;

        $importedData = session('imported_data');
        $columns = $importedData['columns'] ?? [];
        $data = $importedData['data'] ?? [];
        $newStatus = $importedData['new_status'] ?? $defaultStatus;

        $chunkSize = 250;
        $dataChunks = array_chunk($data, $chunkSize);
        unset($data);

        $batchTime = now()->format('d-m-Y H:i:s');

        $unhandledEmails = [];
        $delay = 1;
        foreach ($dataChunks as $chunk) {
            $emails = [];
            $transactions = [];

            foreach ($chunk as $transaction) {
                $email = strtolower($transaction['email']);
                $emails[] = $email;
                $transactions[$email] = $transaction;
            }

            $persons = Person::query()
                ->whereIn('email', $emails)
                ->get();

            foreach ($persons as $person) {

                if ( ! isset($transactions[strtolower($person->email)])) {
                    $unhandledEmails[] = $person->email;
                    continue;
                }

                $transactionUntrimmed = $transactions[strtolower($person->email)];

                // Trim whitespace from column and value to avoid mistakes.
                $transaction = [];
                foreach ($transactionUntrimmed as $columnName => $columnValue) {
                    $transaction[trim($columnName)] = trim($columnValue);
                }

                // Sync with Cint, if relevant
                if (isset($transaction['external_party']) && $transaction['external_party'] === 'CINT') {
                    SyncCintUser::dispatch($person, false)->delay($delay);
                }

                $metaData = [
                    'batch_date'    => $batchTime,
                    'imported_data' => $transaction,
                ];

                if (isset($transaction['external_party'])) {
                    $metaData['external_party'] = $transaction['external_party'];
                }

                // Handle payout
                if (array_key_exists('amount_paid', $transaction) && $transaction['amount_paid'] > 0) {
                    ImportTransaction::dispatch(
                        $person->id,
                        TransactionType::REWARD_PAYOUT,
                        TransactionInitiator::ADMINISTRATOR,
                        -($transaction['amount_paid']),
                        $newStatus,
                        $metaData
                    )->delay($delay);
                }

                // Handle remaining (open) reward
                if (array_key_exists('amount_open', $transaction) && $transaction['amount_open'] > 0) {
                    ImportTransaction::dispatch(
                        $person->id,
                        TransactionType::ACTIVITY_REWARDING,
                        TransactionInitiator::ADMINISTRATOR,
                        $transaction['amount_open'],
                        $newStatus,
                        $metaData
                    )->delay($delay);
                }

                $delay++;
            }
        }

        if ( ! empty($data)) {
            if (empty($unhandledEmails)) {
                Alert::makeSuccess('All transaction are successfully imported, because we found the matching panellist based on the email address reference.');
            } else {
                Alert::makeWarning('Some of the transaction could not be processed so also not accepted imported. This is most likely because of not finding matching panellist in our database based on the email address for that transaction.');
            }
        }

        return view('admin.transactions.import-transactions', compact('columns',
            'unhandledEmails', 'statuses', 'defaultStatus'));
    }

    public function readTransactions()
    {
        $this->authorize('reward-management');

        $rawData = explode("\r\n", trim(request()->get('data')));

        $preparedData = [];
        $columns = [];
        foreach ($rawData as $index => $data) {
            $data = explode("\t", $data);
            unset($rawData[$index]);

            if ($index === 0) {
                $columns = $data;
                continue;
            }

            $preparedData[] = array_combine($columns, $data);
        }

        if (in_array('amount_paid', $columns) && in_array('amount_open', $columns)) {
            Alert::makeWarning('Transaction cannot contain both amount paid and amount open. A transaction can only be one of them.');

            return redirect()->route('admin.transactions.import');
        }

        $newStatus = request()->get('new_status');

        return redirect()->route('admin.transactions.import')
            ->with('imported_data', ['columns' => $columns, 'data' => $preparedData, 'new_status' => $newStatus]);
    }

//    public function undo()
//    {
//        $transactionsQuery = Transaction::query();
//        $transactionsQuery->where('status', TransactionStatus::APPROVED);
//        $transactionsQuery->whereJsonContains('meta_data->external_party', 'CINT');
//        $transactions = $transactionsQuery->count();
//
//        $chunksCount = ($transactions / 5);
//
//        for ($i = 0; $i < $chunksCount; $i++) {
//            UndoCintTransaction::dispatch()->delay($i * 2);
//        }
//    }
}
