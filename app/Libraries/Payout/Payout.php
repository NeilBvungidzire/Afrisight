<?php

namespace App\Libraries\Payout;

use App\Libraries\Payout\Constants\PayoutMethod;

class Payout {

    /**
     * @var float
     */
    private $currentBalance = 0;

    /**
     * @param string $countryCode
     *
     * @return BankAccountTransfer
     */
    public function bankAccount(string $countryCode)
    {
        return new BankAccountTransfer($countryCode);
    }

    /**
     * @param string $countryCode
     * @param string $method
     * @param int|float $amount
     *
     * @return bool
     */
    public function canRequestPayout(string $countryCode, string $method, $amount)
    {
        // Check if this method is actually available for the person in that country.
        $paymentMethod = $this->getPayoutMethod($countryCode, $method);
        if (empty($paymentMethod)) {
            return false;
        }

        if ( ! isset($paymentMethod['minimal_threshold'])) {
            return false;
        }

        // Handle insufficient reward balance, so the user does not reached the threshold amount.
        if ($amount < $paymentMethod['minimal_threshold']) {
            return false;
        }

        switch ($method) {

            case PayoutMethod::BANK_ACCOUNT:
            case PayoutMethod::MOBILE_MONEY:
                // Make sure one or more banks are available for this country to continue.
                return ( ! empty($this->bankAccount($countryCode)->getAvailableBanks()));
        }

        return false;
    }

    /**
     * Get all available payout methods for specified country.
     *
     * @param string $countryCode
     *
     * @return array|array[]
     */
    public function getAvailablePayoutMethods(string $countryCode)
    {
        $availableMethods = Utils::findAvailableMethodsForCountry($countryCode);

        // Add labels to method
        if (isset($availableMethods[PayoutMethod::BANK_ACCOUNT])) {
            $availableMethods[PayoutMethod::BANK_ACCOUNT] = array_merge([
                'name'  => __('payout.method.bank_account.short_name'),
                'label' => __('payout.method.bank_account.long_name'),
            ], $availableMethods[PayoutMethod::BANK_ACCOUNT]);
        } elseif (isset($availableMethods[PayoutMethod::MOBILE_MONEY])) {
            $availableMethods[PayoutMethod::MOBILE_MONEY] = array_merge([
                'name'  => __('payout.method.mobile_money.short_name'),
                'label' => __('payout.method.mobile_money.long_name'),
            ], $availableMethods[PayoutMethod::MOBILE_MONEY]);
        }

        foreach ($availableMethods as $index => $availableMethod) {
            // Make sure the maximum is amount someone can redeem is not higher than the maximum allowed.
            $defaultMaximumAmount = $availableMethods[$index]['maximum_amount'];
            $availableMethods[$index]['maximum_payout_amount'] = $this->currentBalance > $defaultMaximumAmount
                ? $defaultMaximumAmount
                : $this->currentBalance;
        }

        return $availableMethods;
    }

    /**
     * @param string $countryCode
     * @param string $method
     *
     * @return array|null
     */
    public function getPayoutMethod(string $countryCode, string $method)
    {
        $availablePayoutMethods = $this->getAvailablePayoutMethods($countryCode);

        if (isset($availablePayoutMethods[$method])) {
            return $availablePayoutMethods[$method];
        }

        return null;
    }

    /**
     * Set the current balance amount.
     *
     * @param float $amount
     */
    public function setCurrentBalanceAmount(float $amount)
    {
        $this->currentBalance = $amount;
    }
}
