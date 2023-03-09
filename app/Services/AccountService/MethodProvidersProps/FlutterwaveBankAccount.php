<?php

namespace App\Services\AccountService\MethodProvidersProps;

use App\Libraries\APILayer\ExchangeRatesData\ExchangeRateData;
use App\Libraries\Flutterwave\Flutterwave;
use App\Services\AccountService\Contracts\PayoutOptionContract;

class FlutterwaveBankAccount {

    /**
     * @var Flutterwave
     */
    private $flutterwave;

    /**
     * @var PayoutOptionContract
     */
    private $payoutOption;

    /**
     * FlutterwaveBankAccount constructor.
     *
     * @param PayoutOptionContract $payoutOption
     */
    public function __construct(PayoutOptionContract $payoutOption)
    {
        $this->payoutOption = $payoutOption;
        $this->flutterwave = new Flutterwave();
    }

    /**
     * @param float $baseAmount
     * @return float
     */
    public function getLocalAmount(float $baseAmount): float
    {
        if (empty($baseAmount)) {
            return 0.0;
        }

        $response = (new ExchangeRateData())->convert($baseAmount, 'USD', $this->payoutOption->getLocalCurrency());

        if (empty($response)) {
            return 0.0;
        }

        if ( ! isset($response['amount'])) {
            return 0.0;
        }

        return (float)$response['amount'];
    }

    /**
     * @param float $localAmount
     * @return array
     */
    public function getFeeCompensation(float $localAmount): array
    {
        $transferFee = $this->flutterwave->transfers()->getTransferFee($localAmount, $this->payoutOption->getLocalCurrency(), 'account');

        $result = [
            'total_fee'                 => 0,
            'fee_after_compensation'    => 0,
            'amount_after_compensation' => $localAmount,
        ];

        if (isset($transferFee[0]['fee'])) {
            $result['total_fee'] = $transferFee[0]['fee'];
        }

        $result['fee_after_compensation'] = $result['total_fee'] * $this->payoutOption->getFeeCompensation();
        $result['amount_after_compensation'] = $localAmount - $result['fee_after_compensation'];

        return $result;
    }

    /**
     * @return array
     */
    public function getAvailableBanks(): array
    {
        return $this->flutterwave->banks()->getAllBanks($this->payoutOption->getCountryCode(), $this->payoutOption->getMethodName());
    }

    /**
     * @param float $balance
     * @return bool
     */
    public function minimumThresholdReached(float $balance): bool
    {
        return $balance >= $this->payoutOption->getMinTransferLimit();
    }
}
