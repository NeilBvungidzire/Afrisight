<?php

namespace App\Libraries\Reloadly\API;

trait TopUp {

    /**
     * @param int    $operatorId
     * @param float  $baseAmount
     * @param string $recipientPhone
     * @param string $countryCode
     * @param string $customIdentifier
     * @return array|null
     */
    public function requestTopUp(int $operatorId, float $baseAmount, string $recipientPhone, string $countryCode, string $customIdentifier): ?array
    {
        $result = $this->handleRequest('POST', "/topups", [
            'json' => [
                'operatorId'       => $operatorId,
                'amount'           => $baseAmount,
                'useLocalAmount'   => false,
                'customIdentifier' => $customIdentifier,
                'recipientPhone'   => [
                    'countryCode' => $countryCode,
                    'number'      => $recipientPhone,
                ],
            ],
        ]);

        if ($result['status'] === 'success') {
            return $result['body'];
        }

        return null;
    }
}
