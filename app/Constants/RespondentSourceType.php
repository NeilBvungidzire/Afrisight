<?php

namespace App\Constants;

use ReflectionClass;

class RespondentSourceType {

    public const INTERNAL = 'INTERNAL';
    public const EXTERNAL = 'EXTERNAL';

    /**
     * @return array
     */
    public static function getConstants(): array
    {
        return (new ReflectionClass(__CLASS__))->getConstants();
    }
}
