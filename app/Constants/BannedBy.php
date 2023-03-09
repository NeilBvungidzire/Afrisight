<?php

namespace App\Constants;

use ReflectionClass;

class BannedBy {

    public const EMAIL = 'email';
    public const BANK_ACCOUNT = 'bank_account';
    public const MOBILE_NUMBER = 'mobile_number';

    /**
     * @return array
     */
    public static function getConstants(): array {
        return (new ReflectionClass(__CLASS__))->getConstants();
    }
}
