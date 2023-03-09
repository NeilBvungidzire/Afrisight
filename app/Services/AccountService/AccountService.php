<?php

namespace App\Services\AccountService;

use App\CintUser;
use App\Constants\TransactionType;
use App\Country;
use App\Person;
use App\Services\AccountService\Constants\Balances;
use App\Services\AccountService\Constants\TransactionStatus;
use App\Services\AccountService\Contracts\PayoutOptionContract;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Psr\SimpleCache\InvalidArgumentException;

class AccountService {

    use HandlePayoutSession;

    private const availableBalances = [
        Balances::AFRISIGHT,
        Balances::CINT,
    ];

    /**
     * @var Person
     */
    private $person;

    /**
     * AccountService constructor.
     *
     * @param  Person  $person
     */
    public function __construct(Person $person) {
        $this->person = $person;
    }

    /**
     * Check if the person balance needs to be separated from Cint balance.
     *
     * @return bool|null If cannot yet determine, it will return null. Otherwise, boolean depending on.
     */
    public function separateCintBalance(): ?bool {
        $countryCode = $this->getCountryCode();

        if ( ! $countryCode) {
            return null;
        }

        if (in_array($countryCode, ['KE', 'ZA'])) {
            return true;
        }

        return false;
    }

    /**
     * @param  bool  $refresh
     * @param  string  ...$balances  Check available balances.
     * @return float
     */
    public function getBalance(bool $refresh = false, string ...$balances): float {
        if (empty($balances)) {
            $balances = self::availableBalances;
        }

        $balanceAmount = 0;
        foreach ($balances as $balance) {
            if ($refresh) {
                self::clearCachedBalance($this->person->id, $balance);
            }

            $cacheKey = self::getBalanceCacheKey($this->person->id, $balance);
            try {
                $balanceAmount += cache()->remember($cacheKey, now()->addMinutes(60), function () use ($balance) {
                    if ( ! method_exists($this, "get" . ucfirst(strtolower($balance)) . "Balance")) {
                        return 0;
                    }

                    return $this->{"get" . ucfirst(strtolower($balance)) . "Balance"}();
                });
            } catch (Exception $exception) {
                Log::error($exception->getMessage(), $exception->getTrace());
            }
        }

        return (float) $balanceAmount;
    }

    /**
     * Get available payout options for this specific person.
     *
     * @param  string|null  $specificMethod
     * @return PayoutOptionContract[]
     */
    public function getPayoutOptions(string $specificMethod = null): array {
        if ( ! $this->requiredPersonDataAvailable()) {
            return [];
        }

        if ( ! $countryCode = $this->getCountryCode()) {
            Log::error("Country code does not exist for country with id {$this->person->country_id}.");

            return [];
        }

        $payoutMethods = config("payout.options_configs.${countryCode}");
        if (empty($payoutMethods)) {
            return [];
        }

        if ( ! empty($specificMethod)) {
            if (empty($payoutMethods[$specificMethod])) {
                return [];
            }

            $payoutMethods = [
                $specificMethod => $payoutMethods[$specificMethod],
            ];
        }

        $accountParams = $this->person->account_params;
        $customizedPayoutParams = $accountParams['payout'] ?? null;

        $options = [];
        foreach ($payoutMethods as $methodName => $configs) {
            if ( ! is_null($value = $customizedPayoutParams[$methodName]['minimal_threshold'] ?? null)) {
                $configs['minimal_threshold'] = $value;
            }

            if ( ! is_null($value = $customizedPayoutParams[$methodName]['maximum_amount'] ?? null)) {
                $configs['maximum_amount'] = $value;
            }

            $class = '\App\Services\AccountService\PayoutOptionProvider\\' . Str::studly(strtolower($configs['provider'])) . Str::studly(strtolower($methodName)) . 'PayoutOption';
            if ( ! class_exists($class)) {
                continue;
            }

            try {
                $options[$methodName] = new $class($methodName, $configs, $countryCode);
            } catch (Exception $exception) {
                Log::error($exception->getMessage(), $exception->getTrace());

                continue;
            }
        }

        return $options;
    }

    /**
     * @return bool
     */
    public function requiredPersonDataAvailable(): bool {
        return ! empty($this->person->country_id);
    }

    /**
     * @param  int  $personId
     * @param  string  $balance
     * @return string
     */
    public static function getBalanceCacheKey(int $personId, string $balance): string {
        return "PERSON_${personId}_${balance}_BALANCE";
    }

    /**
     * @param  int  $personId
     * @param  string  ...$balances
     */
    public static function clearCachedBalance(int $personId, string ...$balances): void {
        if (empty($balances)) {
            $balances = self::availableBalances;
        }

        foreach ($balances as $balance) {
            try {
                cache()->delete(self::getBalanceCacheKey($personId, $balance));
            } catch (Exception|InvalidArgumentException $exception) {
                Log::error($exception->getMessage(), $exception->getTrace());
            }
        }
    }

    /**
     * @return float
     */
    protected function getAfrisightBalance(): float {
        return (float) $this->person->reward_balance + $this->person->transactions()
                ->whereIn('type', TransactionType::getPayoutTypeConstants())
                ->where(function (Builder $query) {
                    $query->whereIn('status', [TransactionStatus::REQUESTED, TransactionStatus::PENDING]);
                    $query->orWhere(function (Builder $query) {
                        $query->where('status', TransactionStatus::APPROVED);
                        $query->where('balance_adjusted', false);
                    });
                })
                ->sum('amount');
    }

    /**
     * @return float
     */
    protected function getCintBalance(): float {
        if ( ! $cintUserBalance = CintUser::query()->where('person_id', $this->person->id)->value('reward_balance')) {
            return 0.0;
        }

        return (float) $cintUserBalance;
    }

    /**
     * @return string|null
     */
    protected function getCountryCode(): ?string {
        if (empty($this->person->country_id)) {
            return null;
        }

        $countryCode = Country::getCountryIso2Code($this->person->country_id);
        if ( ! $countryCode) {
            Log::error("Country code does not exist for country with id {$this->person->country_id}.");

            return null;
        }

        return $countryCode;
    }
}
