<?php

namespace App\Http\Middleware;

use App\Constants\DataPointAttribute;
use App\UserTrack;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent;

class TrackUserBehaviour {

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $agent = new Agent();
        $deviceType = null;
        if ($agent->isMobile()) {
            $deviceType = DataPointAttribute::MOBILE;
        } elseif ($agent->isTablet()) {
            $deviceType = DataPointAttribute::TABLET;
        } elseif ($agent->isDesktop()) {
            $deviceType = DataPointAttribute::DESKTOP;
        }

        $userAgent = $request->userAgent();
        $ipAddress = last($request->getClientIps());
        $uri = $request->getRequestUri();
        $sessionKey = session()->getId();
        $userId = $request->user() ? $request->user()->id : null;
        $referer = $request->header('referer');
        $metaData = [
            'device_type' => $deviceType,
        ];

        // Set UTM params.
        if ($utmSource = request()->query('utm_source')) {
            $metaData['utm_source'] = $utmSource;
        }
        if ($utmMedium = request()->query('utm_medium')) {
            $metaData['utm_medium'] = $utmMedium;
        }
        if ($utmCampaign = request()->query('utm_campaign')) {
            $metaData['utm_campaign'] = $utmCampaign;
        }

        try {
            UserTrack::create([
                'user_id'     => $userId,
                'ip_address'  => $ipAddress,
                'user_agent'  => $userAgent,
                'session_key' => $sessionKey,
                'uri'         => $uri,
                'referer'     => $referer,
                'meta_data'   => $metaData,
            ]);
        } catch (Exception $exception) {
            Log::error('Could not create UserTrack', [
                'exception_message' => $exception->getMessage(),
                'data'              => [
                    'user_id'     => $userId,
                    'ip_address'  => $ipAddress,
                    'user_agent'  => $userAgent,
                    'session_key' => $sessionKey,
                    'uri'         => $uri,
                    'referer'     => $referer,
                    'meta_data'   => $metaData,
                ],
            ]);
        }

        return $next($request);
    }
}
