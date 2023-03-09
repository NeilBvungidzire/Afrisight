<?php

namespace App\Constants;

use ReflectionClass;

class BlacklistInitiator {

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
