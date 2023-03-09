<?php

namespace App\Libraries\Flutterwave\Utils;

use App\Libraries\Flutterwave\Constants\Currency;

class Transfers extends BaseUtil {

    /**
     * Get applicable transfer fee.
     *
     * @see https://developer.flutterwave.com/reference#get-transfer-fee
     *
     * @param  int|float  $amount
     * @param  string  $currency
     * @param  string  $type  Options: account, mobilemoney.
     *
     * @return array|null
     */
    public function getTransferFee($amount, string $currency, string $type): ?array {
        if (empty($amount) || empty($currency) || empty($type)) {
            return null;
        }

        if ( ! in_array($currency, Currency::getConstants(), true)) {
            return null;
        }

        if ( ! in_array($type, ['account', 'mobilemoney'], true)) {
            return null;
        }

        $result = $this->handleRequest('GET', 'transfers/fee', [
            'query' => [
                'amount'   => $amount,
                'currency' => $currency,
                'type'     => $type,
            ],
        ]);

        return $this->getData($result);
    }

    /**
     * Transfer money to bank account.
     *
     * @see https://developer.flutterwave.com/reference#get-a-transfer
     *
     * @param  string  $currency
     * @param  string  $bankCode
     * @param  string  $accountNumber
     * @param  float  $amount
     * @param  string  $narration
     * @param  string  $beneficiaryName
     * @param  string  $reference
     * @param  string  $debitCurrency
     * @param  array  $metaData
     * @param  string|null  $destinationBranchCode
     *
     * @return array|null
     */
    public function createBankAccountTransfer(
        string $currency,
        string $bankCode,
        string $accountNumber,
        float $amount,
        string $narration,
        string $beneficiaryName,
        string $reference,
        string $debitCurrency,
        array $metaData = [],
        string $destinationBranchCode = null
    ): ?array {
        $data = [
            "account_bank"     => $bankCode,
            "account_number"   => $accountNumber,
            "amount"           => $amount,
            "narration"        => $narration,
            "currency"         => $currency,
            "beneficiary_name" => $beneficiaryName,
            "reference"        => $reference,
            "debit_currency"   => $debitCurrency,
            "meta"             => $metaData,
        ];

        $data['meta']['sender'] = 'AfriSight platform';
        $data['meta']['merchant_name'] = 'AfriSight';

        if ($currency === Currency::ZAR && empty($metaData)) {
            return null;
        }

        if (in_array($currency, [Currency::GHS, Currency::UGX, Currency::TZS], true)) {
            if (empty($destinationBranchCode)) {
                return null;
            }

            $data['destination_branch_code'] = $destinationBranchCode;
        }

        $result = $this->handleRequest('POST', 'transfers', [
            'json' => $data,
        ]);

        return $this->getData($result);
    }

    /**
     * Get a transfer. You can use this to check the status of the transfer request.
     *
     * @param  int  $id
     *
     * @return array|null
     */
    public function getTransfer(int $id): ?array {
        $result = $this->handleRequest('GET', "transfers/${id}");

        return $this->getData($result);
    }
}
