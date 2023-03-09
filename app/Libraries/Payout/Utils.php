<?php

namespace App\Libraries\Payout;

class Utils {

    /**
     * Returns all all available and active payment methods for specified country.
     *
     * @param string $countryCode
     *
     * @return array
     */
    public static function findAvailableMethodsForCountry(string $countryCode)
    {
        $countryCode = strtoupper($countryCode);

        $payoutMethods = config('payout.methods');
        $results = [];
        foreach ($payoutMethods as $method => $configs) {
            $countryMethod = $configs[$countryCode] ?? null;

            if (empty($countryMethod) || empty($countryMethod['active'])) {
                continue;
            }

            $results[$method] = self::setDefaultsIfNeeded($countryMethod);
        }

        return $results;
    }

    /**
     * @param string $countryCode
     * @param string $method
     *
     * @return array|null
     */
    public static function findAvailableMethodForCountry(string $countryCode, string $method)
    {
        return self::findAvailableMethodsForCountry($countryCode)[$method] ?? null;
    }

    /**
     * @param array $methodParams
     *
     * @return array
     */
    private static function setDefaultsIfNeeded(array $methodParams)
    {
        $defaults = config('payout.defaults');

        foreach ($defaults as $defaultPath => $defaultValue) {
            data_fill($methodParams, $defaultPath, $defaultValue);
        }

        return $methodParams;
    }
}
