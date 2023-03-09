<?php

namespace App\Libraries\RewardAccount;

use App\Constants\TransactionType;
use App\Libraries\Payout\Constants\TransactionStatus;
use App\Person;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Psr\SimpleCache\InvalidArgumentException;

class RewardAccountBase {

    /**
     * @var Person
     */
    private $person;

    /**
     * @var string[]
     */
    private $accountTypes = [
        'all',
        'own',
        'cint',
    ];

    /**
     * RewardBalanceBase constructor.
     *
     * @param int $personId
     *
     * @throws Exception
     */
    public function __construct(int $personId)
    {
        $person = Person::withTrashed()->find($personId);

        if ( ! $person) {
            throw new Exception('Person could not be found');
        }

        $this->person = $person;

        return $this;
    }

    /**
     * Return the reward balance as is in database, without taking into account the not yet processed transactions.
     *
     * @param string ...$whichAccounts
     *
     * @return float
     * @throws Exception
     */
    public function getHardRewardBalance(string ...$whichAccounts): float
    {
        $this->prepareChosenAccountsList($whichAccounts);

        $rewardBalance = 0;

        foreach ($whichAccounts as $whichAccount) {
            if (empty($whichAccount)) {
                continue;
            }

            if ( ! in_array($whichAccount, $this->accountTypes)) {
                throw new Exception('Reward account does not exist.');
            }

            if (in_array($whichAccount, ['own', 'all'])) {
                $rewardBalance += (float)$this->person->reward_balance;
            }

            if (in_array($whichAccount, ['cint', 'all'])) {
                $rewardBalance += $this->getRelatedCintUserRewardBalance();
            }
        }

        return (float)$rewardBalance;
    }

    /**
     * Return the reward balance as is in database and taking into account the not yet processed transactions.
     *
     * @param string ...$whichAccounts
     *
     * @return float
     * @throws Exception
     */
    public function getCalculatedRewardBalance(string ...$whichAccounts): float
    {
        $this->prepareChosenAccountsList($whichAccounts);

        $rewardBalance = 0;
        foreach ($whichAccounts as $whichAccount) {
            if (empty($whichAccount)) {
                continue;
            }

            if ( ! in_array($whichAccount, $this->accountTypes)) {
                throw new Exception('Reward account does not exist.');
            }

            if (in_array($whichAccount, ['own', 'all'])) {
                $rewardBalance += $this->getHardRewardBalance('own') + $this->getOwnRewardBalance();
            }

            if (in_array($whichAccount, ['cint', 'all'])) {
                $rewardBalance += $this->getHardRewardBalance('cint');
            }
        }

        return (float)$rewardBalance;
    }

    public static function forceRefresh(int $personId)
    {
        try {
            $cacheKey = self::getCacheKey($personId);
            cache()->delete($cacheKey);
        } catch (Exception | InvalidArgumentException $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
        }
    }

    /**
     * Force data gets retrieved as is and not from cache or somewhere else.
     *
     * @return $this
     */
    public function forceFresh(): RewardAccountBase
    {
        self::forceRefresh($this->person->id);

        return $this;
    }

    /**
     * Make sure to not include other accounts of all accounts is asked for. Otherwise calculation will be done
     * multiple times.
     *
     * @param array $list
     */
    private function prepareChosenAccountsList(array &$list)
    {
        if (empty($list)) {
            $list = ['all'];
            return;
        }

        if (count($list) === 1 && empty($list[0])) {
            $list = ['all'];
            return;
        }

        if (in_array('all', $list)) {
            $list = ['all'];
            return;
        }
    }

    /**
     * Get the sum of all reward payout requests that are waiting to be approved or are approved, but need to be
     * reflected on the balance.
     *
     * @return float
     */
    private function getOwnRewardBalance(): float
    {
        $cacheKey = self::getCacheKey($this->person->id);

        try {
            return (float)cache()->remember($cacheKey, now()->addMinutes(60), function () {
                return $this->person->transactions()
                    ->where('type', TransactionType::REWARD_PAYOUT)
                    ->where(function (Builder $query) {
                        $query->whereIn('status', [TransactionStatus::REQUESTED, TransactionStatus::PENDING]);
                        $query->orWhere(function (Builder $query) {
                            $query->where('status', TransactionStatus::APPROVED);
                            $query->where('balance_adjusted', false);
                        });
                    })
                    ->sum('amount');
            });
        } catch (Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
        }

        return (float)0;
    }

    private static function getCacheKey(int $personId): string
    {
        return "PERSON_${personId}_OWN_WAITING_TRANSACTIONS_SUM";
    }

    /**
     * Check if Cint user also exists and return reward balance.
     *
     * @return float
     */
    private function getRelatedCintUserRewardBalance(): float
    {
        $rewardBalance = 0;

        if ($this->person->cintUser) {
            $rewardBalance = $this->person->cintUser->reward_balance;
        }

        return (float)$rewardBalance;
    }
}
