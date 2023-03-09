<?php

namespace App\Libraries\Flutterwave\Constants;

use ReflectionClass;

class Currency {

    public const USD = 'USD';
    public const ZAR = 'ZAR';
    public const NGN = 'NGN';
    public const UGX = 'UGX';
    public const GHS = 'GHS';
    public const KES = 'KES';
    public const RWF = 'RWF';
    public const TZS = 'TZS';
    public const SLL = 'SLL';
    public const ZMW = 'ZMW';
    public const EUR = 'EUR';
    public const GBP = 'GBP';
    public const XOF = 'XOF';
    public const ETB = 'ETB';

    /**
     * @return array
     */
    public static function getConstants(): array {
        return (new ReflectionClass(__CLASS__))->getConstants();
    }
}
