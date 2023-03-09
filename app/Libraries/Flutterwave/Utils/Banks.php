<?php

namespace App\Libraries\Flutterwave\Utils;

use App\Services\AccountService\Constants\PayoutMethod;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Psr\SimpleCache\InvalidArgumentException;

class Banks extends BaseUtil {

    /**
     * @param string $countryCode ISO Alpha-2 code for country.
     * @param string $bankType
     * @param bool   $passCache
     *
     * @return array|null
     */
    public function getAllBanks(string $countryCode, string $bankType, bool $passCache = false): ?array
    {
        if (empty($countryCode)) {
            return null;
        }

        try {
            $cacheKey = "FLUTTERWAVE_" . strtoupper($countryCode) . "_" . $bankType;

            if ($passCache) {
                cache()->delete($cacheKey);
            }

            return cache()->remember($cacheKey, now()->addDay(), function () use ($countryCode, $bankType) {
                $response = $this->handleRequest('GET', "banks/${countryCode}");
                $allBanks = $this->getData($response);

                $mobileMoneyBanks = [
                    'MPS',
                    'MTN',
                    'TIGO',
                    'VODAFONE',
                    'AIRTEL',
                ];
                $result = [];
                foreach ($allBanks as $bank) {
                    if ( ! isset($bank['code'])) {
                        continue;
                    }

                    // Capitalize bank name.
                    $bank['name'] = strtoupper($bank['name']);

                    $type = null;
                    if (in_array($bank['code'], $mobileMoneyBanks) || Str::contains($bank['name'], $mobileMoneyBanks)) {
                        $type = PayoutMethod::MOBILE_MONEY;
                    } else {
                        $type = PayoutMethod::BANK_ACCOUNT;
                    }

                    // Only get specified bank type
                    if ($type === $bankType) {
                        $result[$bank['id']] = $bank;
                        continue;
                    }
                }

                return $result;
            });
        } catch (Exception | InvalidArgumentException $exception) {
            Log::channel('flutterwave')->error($exception->getMessage(), $exception->getTrace());

            return null;
        }
    }

    /**
     * @param int|string $bankId
     * @param bool $passCache
     *
     * @return array|null
     */
    public function getBranches($bankId, bool $passCache = false): ?array
    {
        try {
            $cacheKey = "FLUTTERWAVE_BANK_" . strtoupper($bankId) . "_BRANCHES";

            if ($passCache) {
                cache()->delete($cacheKey);
            }

            return cache()->remember($cacheKey, now()->addDays(1), function () use ($bankId) {
                $result = $this->handleRequest('GET', "banks/${bankId}/branches");

                return $this->getData($result);
            });
        } catch (Exception | InvalidArgumentException $exception) {
            Log::channel('flutterwave')->error($exception->getMessage(), $exception->getTrace());

            return null;
        }
    }

    /**
     * @param string $countryCode
     * @return bool
     */
    public function bankBranchRequired(string $countryCode): bool
    {
        $bankBranchCodeCountries = ['GH', 'UG', 'TZ'];

        return in_array($countryCode, $bankBranchCodeCountries);
    }
}
