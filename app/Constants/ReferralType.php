<?php

namespace App\Constants;

use ReflectionClass;

class ReferralType {

    public const RESPONDENT_RECRUITMENT = 'RESPONDENT_RECRUITMENT';
    public const PANELLIST_RECRUITMENT = 'PANELLIST_RECRUITMENT';

    /**
     * @return array
     */
    public static function getConstants()
    {
        return (new ReflectionClass(__CLASS__))->getConstants();
    }
}
