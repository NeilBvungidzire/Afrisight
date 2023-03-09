<?php

namespace App\Services\AccountService\Constants;

use ReflectionClass;

class TransactionStatus {

    public const CREATED = 'CREATED';
    public const REQUESTED = 'REQUESTED';
    public const PENDING = 'PENDING';
    public const APPROVED = 'APPROVED';
    public const DENIED = 'DENIED';

    /**
     * @return array
     */
    public static function getConstants(): array {
        return (new ReflectionClass(__CLASS__))->getConstants();
    }

    public static function getEndResultConstants(): array {
        return [
            self::APPROVED,
            self::DENIED,
        ];
    }
}
