<?php

namespace App;

use App\Constants\DataPointAttribute;
use App\Libraries\GeoIPData;
use App\Scopes\IsActiveScope;
use App\Scopes\SortScope;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Country extends Model {

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new IsActiveScope());
        static::addGlobalScope(new SortScope());

        static::addGlobalScope('sortByName', static function (Builder $builder) {
            $builder->orderBy('name', 'asc');
        });
    }

    // ------------------------------------------------------------------------
    // Custom methods
    //

    /**
     * @param int $id
     *
     * @return string|null
     */
    public function getCountryCode(int $id): ?string
    {
        try {
            $countries = cache()->remember('COUNTRY_ALL', now()->addDay(), static function () {
                return self::all()->keyBy('id')->toArray();
            });

            if (isset($countries[$id])) {
                return $countries[$id]['iso_alpha_2'];
            }
        } catch (Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());

            return null;
        }

        return null;
    }

    /**
     * @param int|string $id
     * @return string|null
     */
    public static function getCountryIso2Code($id): ?string
    {
        $cacheKey = "COUNTRY_${id}_CODE";
        try {
            return cache()->remember($cacheKey, now()->addDay(), static function () use ($id) {
                return self::query()
                    ->where('id', $id)
                    ->value('iso_alpha_2');
            });
        } catch (Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
        }

        return null;
    }

    public static function getCountryByIp(string $ip = null): ?Country
    {
        if (empty($ip)) {
            $ip = last(request()->getClientIps());
        }

        if ( ! $ipCountryCode = GeoIPData::lookupForSingle($ip)[DataPointAttribute::COUNTRY_CODE] ?? null) {
            return null;
        }

        $cacheKey = "COUNTRY_BY_CODE_${ipCountryCode}";
        try {
            return cache()->remember($cacheKey, now()->addDay(), static function () use ($ipCountryCode) {
                return self::query()
                    ->where('iso_alpha_2', $ipCountryCode)
                    ->first();
            });
        } catch (Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
        }

        return null;
    }
}
