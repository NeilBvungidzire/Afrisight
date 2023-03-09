<?php

namespace App\Libraries\Payout\Constants;

use ReflectionClass;

/**
 * Class PayoutMethod
 * @package App\Libraries\Payout\Constants
 *
 * Different between mobile money vs mobile wallet @see https://blog.dpogroup.com/mobile-wallets-mobile-money-mpos-differences-business-benefits/
 */
class PayoutMethod {

    const ALTERNATIVE = 'ALTERNATIVE';
    const BANK_ACCOUNT = 'BANK_ACCOUNT';
    const MOBILE_MONEY = 'MOBILE_MONEY';
    const MOBILE_TOP_UP = 'MOBILE_TOP_UP';

    /**
     * @return array
     */
    public static function getConstants()
    {
        $oClass = new ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}
