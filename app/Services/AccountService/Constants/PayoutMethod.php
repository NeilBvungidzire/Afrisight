<?php

namespace App\Services\AccountService\Constants;

use ReflectionClass;

/**
 * Class PayoutMethod
 *
 * @package App\Libraries\Payout\Constants
 *
 * Different between mobile money vs mobile wallet @see
 * https://blog.dpogroup.com/mobile-wallets-mobile-money-mpos-differences-business-benefits/
 */
class PayoutMethod {

    public const ALTERNATIVE = 'ALTERNATIVE';
    public const BANK_ACCOUNT = 'BANK_ACCOUNT';
    public const MOBILE_MONEY = 'MOBILE_MONEY';
    public const MOBILE_TOP_UP = 'MOBILE_TOP_UP';
    public const PAYPAL = 'PAYPAL';

    /**
     * @return array
     */
    public static function getConstants(): array
    {
        return (new ReflectionClass(__CLASS__))->getConstants();
    }
}
