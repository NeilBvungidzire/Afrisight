<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MarketplaceCheck {

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed|void
     */
    public function handle(Request $request, Closure $next)
    {
        if ( ! $this->__isValid($request)) {
            return abort(403);
        }

        if ( ! $marketplaceConfigs = $this->__getConfigs($request['marketplacePublicId'])) {
            return abort(403);
        }

        if ( ! $this->__isActive($marketplaceConfigs)) {
            return abort(403);
        }

        $request['marketplaceConfigs'] = $marketplaceConfigs;

        return $next($request);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    private function __isValid(Request $request)
    {
        if ( ! $marketplacePublicId = $request['marketplacePublicId']) {
            Log::error('Marketplace with public ID is missing in URL.');

            return false;
        }

        if (strlen($marketplacePublicId) !== 8) {
            Log::error('Marketplace with public ID is not exact 8 characters long.');

            return false;
        }

        return true;
    }

    /**
     * @param string $marketplacePublicId
     *
     * @return bool|array
     */
    private function __getConfigs(string $marketplacePublicId)
    {
        $marketplaceConfigs = config('marketplace');
        if (count($marketplaceConfigs) === 0) {
            Log::error('No marketplace is configured.');

            return false;
        }

        foreach ($marketplaceConfigs as $marketplaceConfig) {
            if ($marketplacePublicId === $marketplaceConfig['public_id']) {
                return $marketplaceConfig;
            }
        }

        Log::error("Marketplace with public ID {$marketplacePublicId} is missing in configuration.");

        return false;
    }

    /**
     * @param array $marketplaceConfigs
     *
     * @return bool
     */
    private function __isActive(array $marketplaceConfigs)
    {
        if ($marketplaceConfigs['active']) {
            return true;
        }

        $marketplacePublicId = $marketplaceConfigs['public_id'];
        Log::error("Marketplace with public ID {$marketplacePublicId} does exist, but is not activated.");

        return false;
    }
}
