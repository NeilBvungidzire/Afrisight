<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RedirectsController extends Controller {

    public function completed(string $marketplacePublicId, Request $request)
    {
        Log::info('redirect.completed', [
            'marketplacePublicId' => $marketplacePublicId,
            'url'                 => $request->fullUrl(),
        ]);

        return view('redirects.completed');
    }

    public function failed(string $marketplacePublicId, Request $request)
    {
        Log::info('redirect.failed', [
            'marketplacePublicId' => $marketplacePublicId,
            'url'                 => $request->fullUrl(),
        ]);

        return view('redirects.failed');
    }

    public function quotaReached(string $marketplacePublicId, Request $request)
    {
        Log::info('redirect.quota-reached', [
            'marketplacePublicId' => $marketplacePublicId,
            'url'                 => $request->fullUrl(),
        ]);

        return view('redirects.quota_reached');
    }

    public function disqualified(string $marketplacePublicId, Request $request)
    {
        Log::info('redirect.disqualified', [
            'marketplacePublicId' => $marketplacePublicId,
            'url'                 => $request->fullUrl(),
        ]);

        return view('redirects.disqualified');
    }

    public function closed(string $marketplacePublicId, Request $request)
    {
        Log::info('redirect.closed', [
            'marketplacePublicId' => $marketplacePublicId,
            'url'                 => $request->fullUrl(),
        ]);

        return view('redirects.closed');
    }
}
