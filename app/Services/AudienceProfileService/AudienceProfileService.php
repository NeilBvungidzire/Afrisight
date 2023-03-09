<?php

namespace App\Services\AudienceProfileService;

use GuzzleHttp\Client;

class AudienceProfileService {

    private $client;

    public function __construct() {
        $baseUri = config('services.audience_profile_service.base_uri');
        $accessToken = config('services.audience_profile_service.token');

        if (empty($baseUri) || empty($accessToken)) {
            return;
        }

        $this->client = new Client([
            'base_uri'        => $baseUri,
            'allow_redirects' => false,
            'headers'         => [
                "Authorization" => "Bearer ${accessToken}",
                'Accept'        => 'application/json',
            ],
        ]);
    }

    public function storeSinglePersonDatapoints(int $personId, array $datapoints): void {
        if ( ! isset($this->client)) {
            return;
        }

        $this->client->request('post', "/datapoints/${personId}", [
            'json' => $datapoints,
        ]);
    }

    public function deleteSinglePersonDatapoints(int $personId): void {
        if ( ! isset($this->client)) {
            return;
        }

        $this->client->request('delete', "/datapoints/${personId}");
    }
}