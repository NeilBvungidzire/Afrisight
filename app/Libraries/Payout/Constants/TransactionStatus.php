<?php

namespace App\Libraries\Payout\Constants;

use ReflectionClass;

class TransactionStatus {

    const CREATED = 'CREATED';
    const REQUESTED = 'REQUESTED';
    const PENDING = 'PENDING';
    const APPROVED = 'APPROVED';
    const DENIED = 'DENIED';

    /**
     * @return array
     */
    public static function getConstants(): array
    {
        $oClass = new ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }

    public static function getEndResultConstants(): array
    {
        return [
            self::APPROVED,
            self::DENIED,
        ];
    }
}
