<?php

namespace App\Services\DataPointService;

use App\Constants\DataPointAttribute;
use App\Services\DataPointService\DataRetrieval\RetrieveAgeDataPoint;
use App\Services\DataPointService\DataRetrieval\RetrieveGenderDataPoint;
use App\Services\DataPointService\DataRetrieval\RetrieveSubdivisionCodeDataPoint;

class ExtractDataPoint {

    /**
     * @param int|string $personId
     * @param bool       $fresh
     * @return int|null
     */
    public function getAgeDataPoint($personId, bool $fresh = false): ?int
    {
        return (new RetrieveAgeDataPoint(DataPointAttribute::AGE))
            ->getValue($personId, $fresh);
    }

    /**
     * @param int|string $personId
     * @param bool       $fresh
     * @return string|null
     */
    public function getGenderDataPoint($personId, bool $fresh = false): ?string
    {
        return (new RetrieveGenderDataPoint(DataPointAttribute::GENDER))
            ->getValue($personId, $fresh);
    }

    /**
     * @param int|string $personId
     * @param bool       $fresh
     * @return string|null
     */
    public function getSubdivisionCodeDataPoint($personId, bool $fresh = false): ?string
    {
        return (new RetrieveSubdivisionCodeDataPoint(DataPointAttribute::SUBDIVISION_CODE))
            ->getValue($personId, $fresh);
    }
}
