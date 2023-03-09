<?php

namespace App\Services\AccountService\PayoutOptionProvider;

use App\BankAccount;
use App\Constants\Currency;
use App\Libraries\Flutterwave\Constants\TransferStatus;
use App\Libraries\Flutterwave\Flutterwave;
use App\Services\AccountService\AccountService;
use App\Services\AccountService\Constants\TransactionStatus;
use App\Services\AccountService\Contracts\PayoutOptionContract;
use App\Services\AccountService\MethodProvidersProps\FlutterwaveBankAccount;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Psr\SimpleCache\InvalidArgumentException;

class FlutterwaveBankAccountPayoutOption extends PayoutOptionBase implements PayoutOptionContract {

    /**
     * @param string $callable
     * @param mixed  ...$params
     * @return mixed
     * @throws Exception
     */
    public function getMethodSpecificProps(string $callable, ...$params)
    {
        if ($callable === 'getLocalAmount') {
            if ($this->getProvider() === 'FLUTTERWAVE') {
                return (new FlutterwaveBankAccount($this))->getLocalAmount(...$params);
            }
        }

        if ($callable === 'getFeeCompensation') {
            if ($this->getProvider() === 'FLUTTERWAVE') {
                return (new FlutterwaveBankAccount($this))->getFeeCompensation(...$params);
            }
        }

        if ($callable === 'getAvailableBanks') {
            if ($this->getProvider() === 'FLUTTERWAVE') {
                return (new FlutterwaveBankAccount($this))->getAvailableBanks();
            }
        }

        throw new Exception('Callable does not exist for the provider method.');
    }

    /**
     * @param BankAccount|int|string $bankAccount Either bank account model or bank account ID.
     * @param float                  $baseTransferAmount
     * @param float                  $localTransferAmount
     * @param string                 $transactionType
     * @param string                 $initiator
     * @param string|null            $narration
     * @param array                  $additionalMetaData
     * @return bool
     */
    public function requestPayout(
        $bankAccount,
        float $baseTransferAmount,
        float $localTransferAmount,
        string $transactionType,
        string $initiator,
        string $narration = null,
        array $additionalMetaData = []): bool
    {
        // Try to set the bank account.
        if ((is_int($bankAccount) || is_string($bankAccount)) && ! $bankAccount = BankAccount::find($bankAccount)) {
            return false;
        }
        if ( ! ($bankAccount instanceof BankAccount)) {
            return false;
        }

        $personId = $bankAccount->person_id;

        // Avoid multiple requests within certain amount of time. This is to avoid users requesting from multiple
        // devices and being able to get the amount, before the balance is adjusted.
        try {
            $checkCacheKey = $this->getMethodName() . '_PAYOUT_REQUEST_BEING_PROCESSED_FOR_' . $personId;
            if (cache()->has($checkCacheKey)) {
                return false;
            }
            cache()->add($checkCacheKey, true, now()->addSeconds(60));
        } catch (Exception | InvalidArgumentException $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());

            return false;
        }

        $bankCode = $bankAccount->bank_code;
        $accountNumber = $bankAccount->account_number;
        $beneficiaryName = ($bankAccount->meta_data['first_name'] ?? '') . ' ' . ($bankAccount->meta_data['last_name'] ?? '');
        $uuid = Str::uuid()->toString();
        $debitCurrency = $this->getCustomParams()['debit_currency'] ?? Currency::USD;
        $transfer = (new Flutterwave)->transfers()->createBankAccountTransfer(
            $this->getLocalCurrency(),
            $bankCode,
            $accountNumber,
            $localTransferAmount,
            $narration,
            $beneficiaryName,
            $uuid,
            $debitCurrency,
            [
                "first_name"        => $bankAccount->meta_data['first_name'] ?? null,
                "last_name"         => $bankAccount->meta_data['last_name'] ?? null,
                "email"             => $bankAccount->meta_data['email'] ?? null,
                "mobile_number"     => $bankAccount->meta_data['mobile_number'] ?? null,
                "recipient_address" => $bankAccount->meta_data['recipient_address'] ?? null,
            ],
            $bankAccount->meta_data['bank_branch_code'] ?? null
        );

        $validator = Validator::make((array)$transfer, [
            'id'     => ['required'],
            'status' => ['required', 'string'],
        ]);
        if ($validator->fails()) {
            Log::channel('flutterwave')->error('Transaction invalid.', [
                'transfer' => $transfer,
            ]);

            return false;
        }

        // Map supplier status with our status.
        $transactionState = [
                                TransferStatus::NEW        => TransactionStatus::PENDING,
                                TransferStatus::PENDING    => TransactionStatus::PENDING,
                                TransferStatus::FAILED     => TransactionStatus::DENIED,
                                TransferStatus::SUCCESSFUL => TransactionStatus::APPROVED,
                            ][$transfer['status']] ?? null;

        if ($transactionState === null) {
            Log::channel('flutterwave')->error('Transaction status unknown.', [
                'transfer' => $transfer,
            ]);

            return false;
        }

        try {
            $transaction = $bankAccount->person->transactions()->create([
                'uuid'       => $uuid,
                'type'       => $transactionType,
                'initiator'  => $initiator,
                'amount'     => -($baseTransferAmount),
                'new_status' => $transactionState,
                'meta_data'  => array_merge($additionalMetaData, [
                    'local_currency'  => $this->getLocalCurrency(),
                    'local_amount'    => $localTransferAmount,
                    'bank_account_id' => $bankAccount->id,
                    'debit_currency'  => $debitCurrency,
                    'transfer_id'     => $transfer['id'],
                    'payout_method'   => $this->getMethodName(),
                    'provider'        => $this->getProvider(),
                ]),
            ]);
        } catch (Exception $exception) {
            Log::channel('flutterwave')->error('Transaction could not be created.', [
                'transfer' => $transfer,
            ]);

            return false;
        }

        if ($transaction && in_array($transactionState, [TransactionStatus::PENDING, TransactionStatus::APPROVED], true)) {
            AccountService::clearCachedBalance($personId);

            return true;
        }

        return false;
    }
}
