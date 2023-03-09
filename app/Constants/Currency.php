<?php

namespace App\Constants;

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
    public const MZN = 'MZN';
    public const DZD = 'DZD';
    public const AOA = 'AOA';
    public const BWP = 'BWP';
    public const XAF = 'XAF';
    public const CDF = 'CDF';
    public const EGP = 'EGP';
    public const MAD = 'MAD';
    public const NAD = 'NAD';
    public const ZWD = 'ZWD';
    public const GMD = 'GMD';
    public const LRD = 'LRD';
    public const MWK = 'MWK';
    public const TND = 'TND';

    /**
     * @return array
     */
    public static function getConstants(): array
    {
        return (new ReflectionClass(__CLASS__))->getConstants();
    }
}
