<?php

namespace App\Libraries\Reloadly\API;

use Exception;

trait FXRate {

    /**
     * @param int   $operatorId
     * @param float $amount
     * @param bool  $refresh
     * @return array|null
     */
    public function getFXRate(int $operatorId, float $amount, bool $refresh = false): ?array
    {
        $cacheKey = $this->generateCacheKey('FX_RATE_FOR_' . $operatorId . '_' . $amount);
        try {
            if ($refresh) {
                cache()->forget($cacheKey);
            }

            $result = cache()->remember($cacheKey, now()->addMinutes(10), function () use ($operatorId, $amount) {
                return $this->handleRequest('POST', "/operators/fx-rate", [
                    'json' => [
                        'operatorId'          => $operatorId,
                        'amount'              => $amount,
                    ],
                ]);
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
