<?php

namespace App\Services\AccountService\Constants;

use ReflectionClass;

class Balances {

    public const AFRISIGHT = 'AFRISIGHT';
    public const CINT = 'CINT';

    /**
     * @return array
     */
    public static function getConstants(): array {
        return (new ReflectionClass(__CLASS__))->getConstants();
    }
}
