<?php

namespace App\Libraries\Reloadly\API;

trait Country {

    /**
     * @param string $isoCode
     *
     * @return array|null
     */
    public function getCountryByIsoCode(string $isoCode): ?array
    {
        $result = $this->handleRequest('GET', "/countries/${isoCode}");

        if ($result['status'] === 'success') {
            return $result['body'];
        }

        return null;
    }
}
