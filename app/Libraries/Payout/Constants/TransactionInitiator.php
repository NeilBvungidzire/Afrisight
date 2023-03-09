<?php

namespace App\Libraries\Payout\Constants;

use ReflectionClass;

class TransactionInitiator {

    const ACCOUNT_HOLDER = 'ACCOUNT_HOLDER';
    const ADMINISTRATOR = 'ADMINISTRATOR';
    const PROVIDER = 'PROVIDER';
    const AUTOMATED = 'AUTOMATED';

    /**
     * @return array
     */
    public static function getConstants()
    {
        $oClass = new ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}
