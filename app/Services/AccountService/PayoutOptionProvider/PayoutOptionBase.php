<?php

namespace App\Services\AccountService\PayoutOptionProvider;

use App\Constants\Currency;
use App\Transaction;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PayoutOptionBase {

    /**
     * @var string
     */
    private $methodName;

    /**
     * @var bool
     */
    private $isActive;

    /**
     * @var string
     */
    private $localCurrency;

    /**
     * @var float
     */
    private $minTransferLimit;

    /**
     * @var float
     */
    private $maxTransferLimit;

    /**
     * @var float
     */
    private $feeCompensation;

    /**
     * @var string
     */
    private $provider;

    /**
     * @var array|null
     */
    private $customParams;

    /**
     * @var string
     */
    private $countryCode;

    /**
     * PayoutOption constructor.
     *
     * @param string $methodName
     * @param array  $configs
     * @param string $countryCode
     * @throws Exception
     */
    public function __construct(string $methodName, array $configs, string $countryCode)
    {
        if ( ! $this->isValidMethodConfigs($configs)) {
            throw new Exception('Invalid method configurations.');
        }

        $this->methodName = $methodName;
        $this->isActive = $configs['active'];
        $this->localCurrency = $configs['currency'];
        $this->provider = $configs['provider'];
        $this->minTransferLimit = $configs['minimal_threshold'];
        $this->maxTransferLimit = $configs['maximum_amount'];
        $this->feeCompensation = $configs['fee_compensation'];
        $this->customParams = $configs['params'];
        $this->countryCode = $countryCode;
    }

    /**
     * @param array $configs
     * @return bool
     */
    protected function isValidMethodConfigs(array $configs): bool
    {
        $validator = Validator::make($configs, [
            'active'            => ['required', 'boolean'],
            'currency'          => ['required', Rule::in(Currency::getConstants())],
            'provider'          => ['required', 'string'],
            'minimal_threshold' => ['required', 'numeric'],
            'maximum_amount'    => ['required', 'numeric'],
            'fee_compensation'  => ['required', 'numeric'],
            'params'            => ['array'],
        ]);

        return ! $validator->fails();
    }

    /**
     * @return string
     */
    public function getMethodName(): string
    {
        return $this->methodName;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @return string
     */
    public function getLocalCurrency(): string
    {
        return $this->localCurrency;
    }

    /**
     * @return float
     */
    public function getMinTransferLimit(): float
    {
        return $this->minTransferLimit;
    }

    /**
     * @return float
     */
    public function getMaxTransferLimit(): float
    {
        return $this->maxTransferLimit;
    }

    /**
     * @return string
     */
    public function getProvider(): string
    {
        return $this->provider;
    }

    /**
     * @return float
     */
    public function getFeeCompensation(): float
    {
        return $this->feeCompensation;
    }

    /**
     * @return array|null
     */
    public function getCustomParams(): ?array
    {
        return $this->customParams;
    }

    /**
     * @return string
     */
    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    /**
     * Makes sure the amount requesting is not outside the minimal and maximum ranges and, if required, not higher than
     * amount available on balance.
     *
     * @param float      $requestedTransferAmount
     * @param float|null $balanceAmount
     * @return float
     */
    public function checkAllowedAvailableTransferAmount(float $requestedTransferAmount, float $balanceAmount = null): float
    {
        // Avoid request or balance being zero.
        if (empty($requestedTransferAmount) || ( ! is_null($balanceAmount) && empty($balanceAmount))) {
            return 0;
        }

        $min = $this->getMinTransferLimit();
        $max = $this->getMaxTransferLimit();

        if ( ! is_null($balanceAmount) && $balanceAmount < $min) {
            return 0;
        }

        if ( ! is_null($balanceAmount) && $balanceAmount < $max) {
            $max = $balanceAmount;
        }

        if ($requestedTransferAmount < $min) {
            return 0;
        }

        if ($requestedTransferAmount > $max) {
            return $max;
        }

        return $requestedTransferAmount;
    }

    /**
     * @param float $balance
     * @return bool
     */
    public function minimumThresholdReached(float $balance): bool
    {
        return $balance >= $this->getMinTransferLimit();
    }

    /**
     * @param int         $personId
     * @param string      $type
     * @param string      $initiator
     * @param float       $baseTransferAmount
     * @param string      $status
     * @param string|null $uuid
     * @param array       $additionalMetaData
     * @return Transaction|null
     */
    protected function createPayoutTransaction(
        int    $personId,
        string $type,
        string $initiator,
        float  $baseTransferAmount,
        string $status,
        string $uuid = null,
        array  $additionalMetaData = []): ?Transaction
    {
        $transaction = Transaction::create([
            'person_id'  => $personId,
            'uuid'       => $uuid ?? Str::uuid()->toString(),
            'type'       => $type,
            'initiator'  => $initiator,
            'amount'     => -abs($baseTransferAmount),
            'new_status' => $status,
            'meta_data'  => $additionalMetaData ?? null,
        ]);

        return $transaction ?? null;
    }
}
