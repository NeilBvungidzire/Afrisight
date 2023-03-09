<?php

namespace App\Http\Controllers\Profile;

use App\Constants\DataPointAttribute;
use App\DataPoint;
use App\Http\Controllers\Controller;
use App\Libraries\GeoIPData;
use Exception;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent;

class BaseController extends Controller {

    /**
     * @param int $personId
     */
    protected function setDataPoints(int $personId): void {
        $cacheKey = "PERSON.${personId}.DATA_POINTS_SET";
        try {
            cache()->remember($cacheKey, now()->addDay(), function () use ($personId) {
                // Set device type
                if ($deviceType = $this->getDeviceTypeViaBrowserData()) {
                    DataPoint::saveDatapoint($personId, $deviceType, 1, 'BROWSER');
                }

                // Set GEO IP data
                $ipAddress = last(request()->getClientIps());
                if ( ! empty($ipAddress) && $geoIpDataPoints = GeoIPData::lookupForSingle($ipAddress)) {
                    foreach ($geoIpDataPoints as $dataPointAttribute => $dataPointValue) {
                        DataPoint::saveDatapoint($personId, $dataPointAttribute, $dataPointValue, 'GEO_IP');
                    }
                } else {
                    Log::info("Could not get GEO data for person with ID ${personId} based on IP address ${ipAddress}");

                    return false;
                }

                return true;
            });
        } catch (Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
        }
    }

    /**
     * @return string|null
     */
    private function getDeviceTypeViaBrowserData(): ?string {
        $agent = new Agent();
        $deviceType = null;
        if ($agent->isMobile()) {
            $deviceType = DataPointAttribute::MOBILE;
        } elseif ($agent->isTablet()) {
            $deviceType = DataPointAttribute::TABLET;
        } elseif ($agent->isDesktop()) {
            $deviceType = DataPointAttribute::DESKTOP;
        }

        return $deviceType;
    }
}
