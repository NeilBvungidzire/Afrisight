<?php

namespace App\Constants;

use ReflectionClass;

class Role {

    // General
    public const SUPER_ADMIN = 'SUPER_ADMIN';
    public const ADMIN = 'ADMIN';
    // Web
    public const RESPONDENT = 'RESPONDENT';
    public const TRANSLATOR = 'TRANSLATOR';
    public const MEMBERS_SUPPORT = 'MEMBERS_SUPPORT';
    // API
    public const MARKETPLACE = 'MARKETPLACE';

    /**
     * @return array
     */
    public static function getConstants(): array
    {
        return (new ReflectionClass(__CLASS__))->getConstants();
    }
}
