<?php

namespace App\Constants;

use ReflectionClass;

class InvitationType {

    public const EMAIL = 'EMAIL';
    public const SMS = 'SMS';
    public const APP = 'APP';
    public const INFLOW = 'INFLOW';

    /**
     * @return array
     */
    public static function getConstants(): array
    {
        return (new ReflectionClass(__CLASS__))->getConstants();
    }

    public static function getKeyWithLabel(): array
    {
        return [
            self::EMAIL  => __('general.invitation_type.email'),
            self::SMS    => __('general.invitation_type.sms'),
            self::APP    => __('general.invitation_type.app'),
            self::INFLOW => __('general.invitation_type.inflow'),
        ];
    }
}
