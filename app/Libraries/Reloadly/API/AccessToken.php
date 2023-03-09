<?php

namespace App\Libraries\Reloadly\API;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

trait AccessToken {

    /**
     * @param bool $fresh
     * @return string|null
     * @throws RuntimeException
     */
    protected function getAccessToken(bool $fresh = false): ?string
    {
        $cacheKey = $this->generateCacheKey('GET_ACCESS_TOKEN');

        if ($fresh) {
            Cache::forget($cacheKey);
        }

        $response = Cache::get($cacheKey);

        $authUrl = config('reloadly.auth.url');
        $baseUri = config('reloadly.base_uri');
        $clientId = config('reloadly.auth.id');
        $clientSecret = config('reloadly.auth.secret');

        if (empty($authUrl) || empty($clientId) || empty($clientSecret) || empty($baseUri)) {
            throw new RuntimeException("Reloadly authentication configs not set.");
        }

        if ( ! $response) {
            $this->client = new Client([
                'base_uri'        => $authUrl,
                'allow_redirects' => false,
            ]);

            $response = $this->handleRequest('POST', '', [
                'json' => [
                    "client_id"     => $clientId,
                    "client_secret" => $clientSecret,
                    "grant_type"    => "client_credentials",
                    "audience"      => $baseUri,
                ],
            ]);

            Cache::put($cacheKey, $response, $response['body']['expires_in'] ?? now()->addMinutes(30));
        }

        if ($response['status'] !== 'success') {
            return null;
        }

        return $response['body']['access_token'] ?? null;
    }
}
