<?php

namespace App\Libraries\Payout;

use App\BankAccount;
use App\Libraries\Flutterwave\Constants\Currency;
use App\Libraries\Flutterwave\Flutterwave;
use App\Libraries\Payout\Constants\PayoutMethod;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

abstract class TransferBase {

    /**
     * @var string
     */
    protected $baseCurrency = Currency::USD;

    /**
     * @var string
     */
    protected $payoutMethod;

    /**
     * @var string
     */
    protected $countryCode;

    /**
     * @var Flutterwave
     */
    protected $flutterwave;

    /**
     * BankAccount constructor.
     *
     * @param string $countryCode
     */
    public function __construct(string $countryCode)
    {
        $this->setPayoutMethod();

        $this->flutterwave = new Flutterwave();

        $this->countryCode = $countryCode;
    }

    /**
     * @return void
     */
    abstract protected function setPayoutMethod();

    /**
     * Transfer an amount via specified type.
     *
     * @param int $bankAccountId
     * @param float $baseCurrencyAmount
     * @param float $localCurrencyAmount
     * @param string $type
     * @param string $initiator
     * @param string|null $narration
     * @param string|null $uuid
     * @param array $additionalMetaData
     *
     * @return bool
     */
    abstract public function transfer(
        int $bankAccountId,
        float $baseCurrencyAmount,
        float $localCurrencyAmount,
        string $type,
        string $initiator,
        string $narration = null,
        string $uuid = null,
        array $additionalMetaData = []
    );

    /**
     * @return array|null
     */
    public function getAvailableBanks()
    {
        $mobileMoneyBanks = [
            'MPS',
            'MTN',
            'TIGO',
            'VODAFONE',
            'AIRTEL',
        ];

        $allBanks = $this->flutterwave->banks()->getAllBanks($this->countryCode, $this->payoutMethod);

        $result = [];
        foreach ($allBanks as $bank) {
            if ( ! isset($bank['code'])) {
                continue;
            }

            // Capitalize bank name.
            $bank['name'] = strtoupper($bank['name']);

            $bankType = null;
            if (in_array($bank['code'], $mobileMoneyBanks) || Str::contains($bank['name'], $mobileMoneyBanks)) {
                $bankType = PayoutMethod::MOBILE_MONEY;
            } else {
                $bankType = PayoutMethod::BANK_ACCOUNT;
            }

            // List mobile money banks
            if ($this->payoutMethod === PayoutMethod::MOBILE_MONEY && $bankType === PayoutMethod::MOBILE_MONEY) {
                $result[$bank['id']] = $bank;
                continue;
            }

            // List bank account banks
            if ($this->payoutMethod === PayoutMethod::BANK_ACCOUNT && $bankType === PayoutMethod::BANK_ACCOUNT) {
                $result[$bank['id']] = $bank;
                continue;
            }
        }

        return $result;
    }

    /**
     * @param int $personId
     * @param null|string|string[]|int|int[] $id
     * @param bool $strict Only return bank accounts which are still available in the country.
     *
     * @return Collection
     */
    public function getPersonBankAccounts(int $personId, $id = null, bool $strict = false)
    {
        $bankAccountQuery = BankAccount::query()
            ->where('person_id', $personId)
            ->where('country_code', $this->countryCode)
            ->where('type', $this->payoutMethod);

        if (is_int($id) || is_string($id)) {
            $bankAccountQuery->where('id', $id);
        } elseif (is_array($id)) {
            $bankAccountQuery->whereIn('id', $id);
        }

        $bankAccounts = $bankAccountQuery->get();
        if ($bankAccounts->isEmpty()) {
            return $bankAccounts;
        }

        $banksAvailable = $this->getAvailableBanks();
        $banksAvailable = collect($banksAvailable)->keyBy('code');

        foreach ($bankAccounts as $key => $bankAccount) {
            $bankAccount['available'] = true;

            if ( ! isset($banksAvailable[$bankAccount['bank_code']])) {
                // Remove unavailable banks from list.
                if ($strict) {
                    unset($bankAccounts[$key]);
                    continue;
                }

                $bankAccount['available'] = false;
            } else {
                $bankAccount['name'] = $banksAvailable[$bankAccount['bank_code']]['name'];
            }
        }

        return $bankAccounts;
    }

    /**
     * @param int $personId
     * @param int $bankId
     * @param string $accountNumber
     * @param array $metaData
     * @param int|null $id
     *
     * @return BankAccount
     */
    public function savePersonBankAccount(
        int $personId,
        int $bankId,
        string $accountNumber,
        array $metaData,
        int $id = null
    ) {
        $bankCode = $this->getBankCodeById($bankId);

        $existingData = [
            'person_id'    => $personId,
            'country_code' => $this->countryCode,
            'type'         => $this->payoutMethod,
        ];
        if ($id) {
            $existingData['id'] = $id;
        } else {
            $existingData['bank_code'] = $bankCode;
            $existingData['account_number'] = $accountNumber;
        }

        return BankAccount::updateOrCreate($existingData, [
            'bank_code'      => $bankCode,
            'account_number' => $accountNumber,
            'meta_data'      => $metaData,
        ]);
    }

    /**
     * @param int|float $amount
     * @param string|null $from
     * @param string|null $to
     *
     * @return null|array
     */
    public function fxRates($amount, string $from = null, string $to = null)
    {
        $from = $from ?? $this->baseCurrency;
        $to = $to ?? Utils::findAvailableMethodsForCountry($this->countryCode)[$this->payoutMethod]['currency'] ?? null;
        if (empty($from) || empty($to)) {
            return null;
        }

        $result = $this->flutterwave->miscellaneous()->fxRates($from, $to, $amount);
        if (empty($result)) {
            return null;
        }

        $validator = Validator::make($result, [
            'rate'          => ['required', 'numeric'],
            'from'          => ['required'],
            'to'            => ['required'],
            'from.currency' => ['required', 'string'],
            'to.currency'   => ['required', 'string'],
            'to.amount'     => ['required', 'numeric'],
        ]);

        if ($validator->fails()) {
            return null;
        }

        return [
            'from'   => $result['from']['currency'],
            'to'     => $result['to']['currency'],
            'amount' => (float)number_format($result['to']['amount'], 2, '.', ''),
            'rate'   => (float)number_format($result['rate'], 2, '.', ''),
        ];
    }

    /**
     * @param float $amount
     * @param string|null $currency
     *
     * @return null|array
     */
    public function transferFee(float $amount, string $currency = null)
    {
        $currency = $currency ?? Utils::findAvailableMethodsForCountry($this->countryCode)[$this->payoutMethod]['currency'] ?? null;
        if (empty($currency)) {
            return null;
        }

        $types = [
            PayoutMethod::BANK_ACCOUNT => 'account',
            PayoutMethod::MOBILE_MONEY => 'mobilemoney',
        ];
        $type = $types[$this->payoutMethod] ?? null;
        if ( ! $type) {
            return null;
        }

        $transferFee = $this->flutterwave->transfers()->getTransferFee($amount, $currency, $type);

        $fee = null;
        foreach ($transferFee as $item) {
            // Handle mobile money fee
            if ($this->payoutMethod === PayoutMethod::MOBILE_MONEY) {
                if ( ! isset($item['currency']) || ! $item['currency'] === 'DEFAULT' || ! isset($item['fee'])) {
                    continue;
                }

                $fee = (float)number_format($item['fee'], 2, '.', '');
                break;
            }

            // Handle banks account fee
            if ($this->payoutMethod === PayoutMethod::BANK_ACCOUNT) {
                if ( ! isset($item['currency']) || ! $item['currency'] === $currency || ! isset($item['fee'])) {
                    continue;
                }

                $fee = (float)number_format($item['fee'], 2, '.', '');
                break;
            }
        }

        if ($fee === null) {
            return null;
        }

        $feeCompensation = $this->getFeeCompensation();

        return [
            'currency'        => $currency,
            'total'           => $fee,
            'our_part'        => ($fee * $feeCompensation),
            'respondent_part' => ($fee * (1 - $feeCompensation)),
        ];
    }

    /**
     * @return float|null
     */
    public function getThresholdAmount()
    {
        $availableMethods = Utils::findAvailableMethodsForCountry($this->countryCode);

        // Add labels to method
        if ( ! isset($availableMethods[$this->payoutMethod])) {
            return null;
        }

        $threshold = $availableMethods[$this->payoutMethod]['minimal_threshold'] ?? null;
        if ($threshold) {
            return (float)number_format($threshold, 2, '.', '');
        }

        return null;
    }

    /**
     * @param int $id
     *
     * @return string|null
     */
    protected function getBankCodeById(int $id)
    {
        foreach ($this->getAvailableBanks() as $availableBank) {
            if ($availableBank['id'] == $id) {
                return $availableBank['code'];
            }
        }

        return null;
    }

    /**
     * @return float
     */
    protected function getFeeCompensation()
    {
        $availableMethod = Utils::findAvailableMethodForCountry($this->countryCode, $this->payoutMethod);

        // Add labels to method
        if ( ! $availableMethod) {
            return (float)0;
        }

        $feeCompensationPercentage = $availableMethod['fee_compensation'] ?? null;
        if ($feeCompensationPercentage) {
            return (float)$feeCompensationPercentage;
        }

        return (float)0;
    }

    protected function getDebitCurrency()
    {
        $availableMethod = Utils::findAvailableMethodForCountry($this->countryCode, $this->payoutMethod);

        return isset($availableMethod['params']['debit_currency'])
            ? $availableMethod['params']['debit_currency']
            : $this->baseCurrency;
    }
}
