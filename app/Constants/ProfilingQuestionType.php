<?php

namespace App\Constants;

use ReflectionClass;

class ProfilingQuestionType {

    public const DROPDOWN = 'DROPDOWN';
    public const MULTIPLE_CHOICE = 'MULTIPLE_CHOICE';
    public const CHECKBOXES = 'CHECKBOXES';
    public const SINGLE_TEXT_BOX = 'SINGLE_TEXT_BOX';

    /**
     * @return array
     */
    public static function getConstants(): array
    {
        return (new ReflectionClass(__CLASS__))->getConstants();
    }
}
