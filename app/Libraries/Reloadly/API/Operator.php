<?php

namespace App\Libraries\Reloadly\API;

use Exception;

trait Operator {

    /**
     * @param string $phoneNumber    Phone number with country prefix.
     * @param string $countryIsoCode ISO 3166-1 alpha-2 Country code.
     * @param bool   $refresh
     * @return array|null
     * @see https://developers.reloadly.com/api.html#auto-detect-mobile-operator
     */
    public function getOperatorByMobileNumber(string $phoneNumber, string $countryIsoCode, bool $refresh = false): ?array
    {
        $cacheKey = $this->generateCacheKey('MOBILE_OPERATORS_BY_PHONE_NUMBER_' . $phoneNumber);
        try {
            if ($refresh) {
                cache()->forget($cacheKey);
            }

            $result = cache()->remember($cacheKey, now()->minutes(30), function () use ($phoneNumber, $countryIsoCode) {
                return $this->handleRequest('GET', "/operators/auto-detect/phone/${phoneNumber}/countries/${countryIsoCode}");
            });
        } catch (Exception $exception) {
            return null;
        }

        if ($result['status'] === 'success') {
            return $result['body'];
        }

        return null;
    }

    /**
     * @param string $countryIsoCode ISO 3166-1 alpha-2 Country code.
     * @param bool   $refresh
     * @return array|null
     * @see https://developers.reloadly.com/api.html#list-available-operators
     */
    public function getOperatorsByCountryCode(string $countryIsoCode, bool $refresh = false): ?array
    {
        $cacheKey = $this->generateCacheKey('MOBILE_OPERATORS_BY_COUNTRY_' . $countryIsoCode);
        try {
            if ($refresh) {
                cache()->forget($cacheKey);
            }

            $result = cache()->remember($cacheKey, now()->addMinutes(30), function () use ($countryIsoCode) {
                return $this->handleRequest('GET', "/operators/countries/${countryIsoCode}");
            });
        } catch (Exception $exception) {
            return null;
        }

        if ($result['status'] === 'success') {
            return $result['body'];
        }

        return null;
    }
}
