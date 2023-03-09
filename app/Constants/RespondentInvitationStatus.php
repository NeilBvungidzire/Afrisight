<?php

namespace App\Constants;

use ReflectionClass;

class RespondentInvitationStatus {

    public const SEND = 'SEND';
    public const OPENED = 'OPENED';
    public const DISPLAYED = 'DISPLAYED';
    public const REDIRECTED = 'REDIRECTED';

    /**
     * @return array
     */
    public static function getConstants(): array
    {
        return (new ReflectionClass(__CLASS__))->getConstants();
    }
}
