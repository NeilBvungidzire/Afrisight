<?php

namespace App\Services\AccountService\Contracts;

interface PayoutOptionContract {

    /**
     * @return string
     */
    public function getMethodName(): string;

    /**
     * @return bool
     */
    public function isActive(): bool;

    /**
     * @return string
     */
    public function getLocalCurrency(): string;

    /**
     * @return float
     */
    public function getMinTransferLimit(): float;

    /**
     * @return float
     */
    public function getMaxTransferLimit(): float;

    /**
     * @return string
     */
    public function getProvider(): string;

    /**
     * @return float
     */
    public function getFeeCompensation(): float;

    /**
     * @return array|null
     */
    public function getCustomParams(): ?array;

    /**
     * @return string
     */
    public function getCountryCode(): string;

    /**
     * @param float $balance
     * @return bool
     */
    public function minimumThresholdReached(float $balance): bool;

    /**
     * Make sure the amount requesting is not outside the minimal and maximum ranges and or balance.
     *
     * @param float      $requestedTransferAmount
     * @param float|null $balanceAmount
     * @return float
     */
    public function checkAllowedAvailableTransferAmount(float $requestedTransferAmount, float $balanceAmount = null): float;
}
