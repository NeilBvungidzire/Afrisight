<?php

namespace App\Cint;

use App\Libraries\Hateoas\HateoasClient;
use Exception;

class CintApi extends HateoasClient {

    use PanelApi, PanelistApi, GeneralResourceApi, RespondentApi;

    private $headers;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $baseUri = config('cint.base_url');
        if (empty($baseUri)) {
            throw new Exception(__('Base URI not set.'));
        }

        $clientConfigs = [
            'base_uri' => $baseUri,
            'headers'  => [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ],
        ];

        return parent::__construct($clientConfigs);
    }
}
