<?php

namespace App\Services\AccountService\Constants;

use ReflectionClass;

class Provider {

    public const FLUTTERWAVE = 'FLUTTERWAVE';
    public const RELOADLY = 'RELOADLY';
    public const CINT = 'CINT';
    public const BEL_CASH = 'BEL_CASH';

    /**
     * @return array
     */
    public static function getConstants(): array
    {
        return (new ReflectionClass(__CLASS__))->getConstants();
    }
}
