<?php

namespace App\Libraries;

use App\Constants\DataPointAttribute;
use Exception;
use Illuminate\Support\Facades\Log;
use Ipdata\ApiClient\Ipdata;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Component\HttpClient\Psr18Client;

class GeoIPData {

    /**
     * @param string $ip
     *
     * @return array|null
     */
    public static function lookupForSingle(string $ip): ?array
    {
        try {
            if ( ! $ipDataInstance = self::getIPDataInstance()) {
                return null;
            }
            $result = cache()->remember("IP_LOOKUP_FOR_${ip}", now()->addDay(), static function () use ($ipDataInstance, $ip) {
                return $ipDataInstance->lookup($ip);
            });

            if ( ! array_key_exists('status', $result) || $result['status'] !== 200) {
                return null;
            }

            return self::prepareGeoResult($result);
        } catch (Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
        }

        return null;
    }

    private static function prepareGeoResult(array $geoIPData): array
    {
        $result = [];

        // Country
        if (isset($geoIPData['country_code'])) {
            $result[DataPointAttribute::COUNTRY_CODE] = $geoIPData['country_code'];
        }

        // ISO-3166-2 subdivision
//        if (isset($geoIPData['region_code']) && isset($geoIPData['country_code'])) {
//            $result[DataPointAttribute::SUBDIVISION_CODE] = $geoIPData['country_code'] . '-' . $geoIPData['region_code'];
//        }

        // City
//        if (isset($geoIPData['city'])) {
//            $result[DataPointAttribute::CITY_NAME] = $geoIPData['city'];
//        }

        // Geographic coordinate
//        if (isset($geoIPData['latitude']) && isset($geoIPData['longitude'])) {
//            $value = 'lat:' . $geoIPData['latitude'] . '|long:' . $geoIPData['longitude'];
//            $result[DataPointAttribute::GEO_COORDINATES] = $value;
//        }

        // Known threat
        if (isset($geoIPData['threat'])) {
            $isThreat = 0;
            foreach ($geoIPData['threat'] as $threat) {
                if ($threat) {
                    $isThreat = 1;
                }
            }
            $result[DataPointAttribute::THREAT] = $isThreat;
        }

        return $result;
    }

    /**
     * @return Ipdata|null
     */
    private static function getIPDataInstance(): ?Ipdata
    {
        $httpClient = new Psr18Client();
        $psr17Factory = new Psr17Factory();

        $apiKey = config('services.ipdata.api_key');
        if (empty($apiKey)) {
            return null;
        }

        return new Ipdata($apiKey, $httpClient, $psr17Factory);
    }
}
