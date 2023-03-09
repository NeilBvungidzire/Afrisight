<?php

namespace App\Services\DataPointService;

use App\Constants\DataPointAttribute;
use App\DataPoint;

class PlaceDataPoint {

    /**
     * @param  int|string  $personId
     * @param  int|string  $value  Numeric value representing the age.
     * @param  string  $sourceType
     * @return void
     */
    public function setAgeDataPoint($personId, $value, string $sourceType): void {
        if (empty($personId) || empty($value)) {
            return;
        }

        DataPoint::saveDatapoint(
            $personId,
            DataPointAttribute::AGE,
            (string) $value,
            $sourceType
        );
    }

    /**
     * @param  int|string  $personId
     * @param  string  $value
     * @param  string  $sourceType
     * @return void
     */
    public function setGenderDataPoint($personId, string $value, string $sourceType): void {
        if (empty($personId) || empty($value)) {
            return;
        }

        DataPoint::saveDatapoint(
            $personId,
            DataPointAttribute::GENDER,
            $value,
            $sourceType
        );
    }

    /**
     * @param  int|string  $personId
     * @param  string  $value  ISO 3166-2 code for that specific subdivision.
     * @param  string  $sourceType
     * @return void
     */
    public function setSubdivisionCodeDataPoint($personId, string $value, string $sourceType): void {
        if (empty($personId) || empty($value)) {
            return;
        }

        DataPoint::saveDatapoint(
            $personId,
            DataPointAttribute::SUBDIVISION_CODE,
            $value,
            $sourceType
        );
    }
}
