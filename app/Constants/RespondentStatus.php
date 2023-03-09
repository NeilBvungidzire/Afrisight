<?php

namespace App\Constants;

use ReflectionClass;

class RespondentStatus {

    public const SELECTED = 'SELECTED';
    public const RESELECTED = 'RESELECTED';
    public const INVITED = 'INVITED';
    public const REMINDED = 'REMINDED';
    public const ENROLLING = 'ENROLLING';
    public const TARGET_SUITABLE = 'TARGET_SUITABLE';
    public const TARGET_UNSUITABLE = 'TARGET_UNSUITABLE';
    public const STARTED = 'STARTED';
    public const ABANDONED = 'ABANDONED';
    public const COMPLETED = 'COMPLETED';
    public const DISQUALIFIED = 'DISQUALIFIED';
    public const SCREEN_OUT = 'SCREEN_OUT';
    public const POST_DISQUALIFIED = 'POST_DISQUALIFIED';
    public const QUOTA_FULL = 'QUOTA_FULL';
    public const CLOSED = 'CLOSED';

    /**
     * @return array
     */
    public static function getConstants(): array
    {
        return (new ReflectionClass(__CLASS__))->getConstants();
    }
}
