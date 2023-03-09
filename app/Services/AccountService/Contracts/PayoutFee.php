<?php

namespace App\Services\AccountService\Contracts;

class PayoutFee {

    /**
     * @var float
     */
    private $feeCompensationPercentage;

    /**
     * @var float
     */
    private $feeCompensationAmount;

    /**
     * @var float
     */
    private $feeAmount;

    /**
     * PayoutFee constructor.
     *
     * @param float $feeAmount
     * @param float $feeCompensationPercentage
     * @param float $feeCompensationAmount
     */
    public function __construct(float $feeAmount, float $feeCompensationPercentage, float $feeCompensationAmount)
    {
        $this->feeAmount = $feeAmount;
        $this->feeCompensationPercentage = $feeCompensationPercentage;
        $this->feeCompensationAmount = $feeCompensationAmount;
    }

    /**
     * @return float
     */
    public function getFeeCompensationPercentage(): float
    {
        return $this->feeCompensationPercentage;
    }

    /**
     * @return float
     */
    public function getFeeCompensationAmount(): float
    {
        return $this->feeCompensationAmount;
    }

    /**
     * @return float
     */
    public function getFeeAmount(): float
    {
        return $this->feeAmount;
    }

    /**
     * @param float $feeCompensationPercentage
     */
    public function setFeeCompensationPercentage(float $feeCompensationPercentage): void
    {
        $this->feeCompensationPercentage = $feeCompensationPercentage;
    }
}
