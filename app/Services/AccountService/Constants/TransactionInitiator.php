<?php

namespace App\Services\AccountService\Constants;

use ReflectionClass;

class TransactionInitiator {

    public const ACCOUNT_HOLDER = 'ACCOUNT_HOLDER';
    public const ADMINISTRATOR = 'ADMINISTRATOR';
    public const PROVIDER = 'PROVIDER';
    public const AUTOMATED = 'AUTOMATED';

    /**
     * @return array
     */
    public static function getConstants(): array {
        return (new ReflectionClass(__CLASS__))->getConstants();
    }
}
