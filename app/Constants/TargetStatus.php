<?php

namespace App\Constants;

use ReflectionClass;

class TargetStatus {

    public const OPEN = 'OPEN';
    public const PAUSED = 'PAUSED';
    public const CLOSED = 'CLOSED';

    /**
     * @return array
     */
    public static function getConstants(): array
    {
        return (new ReflectionClass(__CLASS__))->getConstants();
    }
}
