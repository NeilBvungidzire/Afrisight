<?php

namespace App\Cint;

use App\CintUser;
use App\Jobs\SyncCintUser;
use Exception;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Log;
use Psr\SimpleCache\InvalidArgumentException;

trait HasCintRelationship {

    // ------------------------------------------------------------------------
    // Related models
    //

    /**
     * @return HasOne
     */
    public function cintUser()
    {
        return $this->hasOne(CintUser::class);
    }

    // ------------------------------------------------------------------------
    // Mutators
    //

    /**
     * When Cint payment option is available, the reward redemption cannot be done from both parties.
     *
     * @return bool
     */
    public function getCanCombineWithCintRewardBalanceAttribute()
    {
        if (empty($this->country_id)) {
            return false;
        }

        $isoAlpha2 = $this->country->iso_alpha_2;
        if (empty($isoAlpha2)) {
            return false;
        }

        return ! CintUser::canRequestPayout($isoAlpha2);
    }

    // ------------------------------------------------------------------------
    // Custom methods
    //

    /**
     * @param bool $force
     */
    public function syncCintData(bool $force = false)
    {
        $cacheKey = "PERSON_{$this->id}_CINT_DATA_SYNCED";
        $person = $this;

        try {
            if ($force) {
                cache()->delete($cacheKey);
            }

            cache()->remember($cacheKey, now()->addDays(1), function () use ($person) {
                SyncCintUser::dispatchNow($person);

                return true;
            });
        } catch (Exception $exception) {
            Log::channel('cint')->error($exception->getMessage(), $exception->getTrace());
        } catch (InvalidArgumentException $exception) {
            Log::channel('cint')->error($exception->getMessage(), $exception->getTrace());
        }
    }
}
