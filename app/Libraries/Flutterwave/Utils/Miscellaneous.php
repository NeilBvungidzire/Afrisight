<?php

namespace App\Libraries\Flutterwave\Utils;

class Miscellaneous extends BaseUtil {

    /**
     * Get Flutterwave transfer rates when sending money from your Flutterwave wallet to a different currency.
     *
     * @param string $from
     * @param string $to
     * @param int|float $amount
     *
     * @return array
     */
    public function fxRates(string $from, string $to, $amount)
    {
        if (empty($from) || empty($to) || empty($amount)) {
            return null;
        }

        $result = $this->handleRequest('GET', 'rates', [
            'query' => [
                'from'   => $from,
                'to'     => $to,
                'amount' => $amount,
            ],
        ]);

        return $this->getData($result);
    }
}
