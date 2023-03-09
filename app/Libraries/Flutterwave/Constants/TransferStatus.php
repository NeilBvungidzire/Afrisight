<?php

namespace App\Libraries\Flutterwave\Constants;

use ReflectionClass;

class TransferStatus {

    public const NEW = 'NEW';
    public const PENDING = 'PENDING';
    public const SUCCESSFUL = 'SUCCESSFUL';
    public const FAILED = 'FAILED';
    public const UNKNOWN = 'UNKNOWN';

    /**
     * @return array
     */
    public static function getConstants(): array {
        return (new ReflectionClass(__CLASS__))->getConstants();
    }
}
