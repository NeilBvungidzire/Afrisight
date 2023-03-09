<?php

namespace App\Libraries\Payout;

use App\BankAccount;
use App\Libraries\Flutterwave\Constants\TransferStatus;
use App\Libraries\Payout\Constants\PayoutMethod;
use App\Libraries\Payout\Constants\TransactionStatus;
use App\Libraries\RewardAccount\RewardAccountBase;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Psr\SimpleCache\InvalidArgumentException;

class BankAccountTransfer extends TransferBase {

    protected function setPayoutMethod()
    {
        $this->payoutMethod = PayoutMethod::BANK_ACCOUNT;
    }

    /**
     * @param int|string $bankId
     *
     * @return array|null
     */
    public function getBankBranches($bankId)
    {
        return $this->flutterwave->banks()->getBranches($bankId);
    }

    /**
     * @return bool
     */
    public function bankBranchRequired()
    {
        $bankBranchCodeCountries = ['GH', 'UG', 'TZ'];

        return in_array($this->countryCode, $bankBranchCodeCountries);
    }

    /**
     * @param int         $bankAccountId
     * @param float       $baseCurrencyAmount
     * @param float       $localCurrencyAmount
     * @param string      $type
     * @param string      $initiator
     * @param string|null $narration
     * @param string|null $uuid UUID
     * @param array       $additionalMetaData
     *
     * @return bool
     */
    public function transfer(
        int $bankAccountId,
        float $baseCurrencyAmount,
        float $localCurrencyAmount,
        string $type,
        string $initiator,
        string $narration = null,
        string $uuid = null,
        array $additionalMetaData = []
    ): bool
    {
        $method = Utils::findAvailableMethodsForCountry($this->countryCode);
        if (empty($method) || ! isset($method[$this->payoutMethod]['currency'])) {
            return false;
        }

        $method = $method[$this->payoutMethod];

        $localCurrency = $method['currency'];
        $bankAccount = BankAccount::find($bankAccountId);
        $bankCode = $bankAccount->bank_code;
        $accountNumber = $bankAccount->account_number;
        $debitCurrency = $this->getDebitCurrency();
        $personId = $bankAccount->person_id;
        $beneficiaryName = ($bankAccount->meta_data['first_name'] ?? '') . ' ' . ($bankAccount->meta_data['last_name'] ?? '');
        $uuid = $uuid ?? Str::uuid()->toString();

        // Avoid multiple requests within certain amount of time. This is to avoid users requesting from multiple
        // devices and being able to get the amount, before the balance is adjusted.
        try {
            $checkCacheKey = 'PAYOUT_REQUEST_BEING_PROCESSED_FOR_' . $personId;
            if (cache()->has($checkCacheKey)) {
                return false;
            }
            cache()->add($checkCacheKey, true, now()->addSeconds(5));
        } catch (Exception | InvalidArgumentException $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());

            return false;
        }

        $transfer = $this->flutterwave->transfers()->createBankAccountTransfer(
            $localCurrency,
            $bankCode,
            $accountNumber,
            $localCurrencyAmount,
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

        $transaction = $bankAccount->person->transactions()->create([
            'uuid'       => $uuid,
            'type'       => $type,
            'initiator'  => $initiator,
            'amount'     => -($baseCurrencyAmount),
            'new_status' => $transactionState,
            'meta_data'  => array_merge($additionalMetaData, [
                'local_currency'   => $localCurrency,
                'local_amount'     => $localCurrencyAmount,
                'transaction_type' => $this->payoutMethod,
                'bank_account_id'  => $bankAccount->id,
                'debit_currency'   => $debitCurrency,
                'transfer_id'      => $transfer['id'],
                'payout_method'    => PayoutMethod::BANK_ACCOUNT,
                'provider'         => isset($method['provider']) ? $method['provider'] : null,
            ]),
        ]);

        if ($transaction) {
            RewardAccountBase::forceRefresh($personId);
        }

        return in_array($transactionState, [
            TransactionStatus::PENDING,
            TransactionStatus::APPROVED,
        ]);
    }
}
