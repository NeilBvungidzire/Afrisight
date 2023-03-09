<?php

namespace App\Constants;

use ReflectionClass;

class DataPointAttribute {

    // Personal info
    public const AGE = 'AGE';
    public const DATE_OF_BIRTH = 'DATE_OF_BIRTH';
    public const GENDER = 'GENDER';
    public const RACE = 'RACE';
    // Geo
    public const COUNTRY_CODE = 'COUNTRY_CODE';
    public const REGION_CODE = 'REGION_CODE';
    public const SUBDIVISION_CODE = 'SUBDIVISION_CODE';
    public const CITY_NAME = 'CITY_NAME';
    public const GEO_COORDINATES = 'GEO_COORDINATES';
    public const URBAN_RURAL = 'URBAN_RURAL';
    // Device type
    public const MOBILE = 'MOBILE';
    public const TABLET = 'TABLET';
    public const DESKTOP = 'DESKTOP';
    // Threat
    public const THREAT = 'THREAT';

    /**
     * @return array
     */
    public static function getConstants(): array
    {
        return (new ReflectionClass(__CLASS__))->getConstants();
    }
}
