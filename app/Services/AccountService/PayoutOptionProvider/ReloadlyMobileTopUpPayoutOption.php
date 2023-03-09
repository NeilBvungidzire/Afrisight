<?php

namespace App\Services\AccountService\PayoutOptionProvider;

use App\Constants\Currency;
use App\Constants\TransactionType;
use App\Libraries\Reloadly\Reloadly;
use App\Services\AccountService\Constants\TransactionStatus;
use App\Services\AccountService\Contracts\PayoutOptionContract;
use Illuminate\Support\Str;

class ReloadlyMobileTopUpPayoutOption extends PayoutOptionBase implements PayoutOptionContract {

    public function requestPayout(
        int $personId,
        string $initiator,
        float $baseTransferAmount,
        int $operatorId,
        string $recipientPhone,
        string $type = null,
        array $additionalMetaData = []
    ): bool
    {
        $countryCode = $this->getCountryCode();
        $uuid = Str::uuid()->toString();

        $reloadly = new Reloadly();
        $transfer = $reloadly->requestTopUp($operatorId, $baseTransferAmount, $recipientPhone, $countryCode, $uuid);

        $status = empty($transfer) ? TransactionStatus::DENIED : TransactionStatus::APPROVED;
        if (empty($type)) {
            $type = TransactionType::REWARD_PAYOUT;
        }
        $debitCurrency = $this->getCustomParams()['debit_currency'] ?? Currency::USD;

        $this->createPayoutTransaction($personId, $type, $initiator, $baseTransferAmount, $status, $uuid, array_merge($additionalMetaData, [
            'local_currency' => $this->getLocalCurrency(),
            'debit_currency' => $debitCurrency,
            'transaction_id' => $transfer['transactionId'],
            'payout_method'  => $this->getMethodName(),
            'provider'       => $this->getProvider(),
            'phone_number'   => $recipientPhone,
        ]));

        return ! empty($transfer);
    }
}
