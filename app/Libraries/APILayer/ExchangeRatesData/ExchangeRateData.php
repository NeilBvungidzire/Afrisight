<?php

namespace App\Libraries\APILayer\ExchangeRatesData;

use App\Libraries\APILayer\BaseUtils;
use Exception;

class ExchangeRateData extends BaseUtils {

    protected function getMarketplaceName(): string {
        return 'exchangerates_data';
    }

    public function getData(array $response): ?array {
        if ($response['success'] ?? false) {
            return $this->handleSuccess($response);
        }

        return $this->handleFailure($response);
    }

    /**
     * @param  float  $amount
     * @param  string  $from
     * @param  string  $to
     * @param  bool  $fresh
     * @return array|null
     */
    public function convert(float $amount, string $from, string $to, bool $fresh = false): ?array {
        if (empty($from) || empty($to) || empty($amount)) {
            return null;
        }

        $requestParams = [
            'GET',
            '/convert',
            [
                'query' => [
                    'from'   => $from,
                    'to'     => $to,
                    'amount' => $amount,
                ],
            ],
        ];
        $cacheKey = $this->generateCacheKey($requestParams);

        try {
            $cachedData = cache()->get($cacheKey);

            if ($fresh || empty($cachedData)) {
                cache()->forget($cacheKey);
            }

            if ( ! ($cachedData['success'] ?? false)) {
                cache()->forget($cacheKey);
            }

            return cache()->remember($cacheKey, now()->addHours(6), function () use ($requestParams) {
                return $this->getData($this->handleRequest(...$requestParams));
            });
        } catch (Exception $exception) {
            return null;
        }
    }

    /**
     * @param  array  $result
     * @return array|null
     */
    private function handleSuccess(array $result): ?array {
        if ( ! isset($result['result'])) {
            return null;
        }

        return [
            'success' => true,
            'amount'  => $result['result'],
            'rate'    => $result['info']['rate'] ?? null,
        ];
    }

    /**
     * @param  array  $result
     * @return array|null
     */
    private function handleFailure(array $result): ?array {
        if ( ! isset($result['error'])) {
            return null;
        }

        return [
            'success' => false,
            'code'    => $result['error']['code'] ?? null,
            'message' => $result['error']['message'] ?? null,
        ];
    }
}