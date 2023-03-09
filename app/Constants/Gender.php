<?php

namespace App\Constants;

use ReflectionClass;

class Gender {

    public const MAN = 'm';
    public const WOMAN = 'w';
    public const UNDEFINED = 'u';

    /**
     * @return array
     */
    public static function getKeyWithLabel(): array
    {
        return [
            self::MAN       => __('general.gender.male'),
            self::WOMAN     => __('general.gender.female'),
            self::UNDEFINED => __('general.gender.undefined'),
        ];
    }

    /**
     * @return array
     */
    public static function getConstants(): array
    {
        return (new ReflectionClass(__CLASS__))->getConstants();
    }
}
