<?php

namespace App\Services\DataPointService;

class DataPointService {

    /**
     * @return ExtractDataPoint
     */
    public function extract(): ExtractDataPoint
    {
        return new ExtractDataPoint();
    }

    /**
     * @return PlaceDataPoint
     */
    public function place(): PlaceDataPoint
    {
        return new PlaceDataPoint();
    }
}
