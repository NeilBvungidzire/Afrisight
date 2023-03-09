<?php

namespace App\Libraries\Reloadly;

use App\Libraries\Reloadly\API\AccessToken;
use App\Libraries\Reloadly\API\Country;
use App\Libraries\Reloadly\API\FXRate;
use App\Libraries\Reloadly\API\Operator;
use App\Libraries\Reloadly\API\TopUp;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;
use RuntimeException;

class Reloadly {

    use AccessToken, Operator, Country, FXRate, TopUp;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string|null
     */
    private $accessToken;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $baseUri = config('reloadly.base_uri');
        $clientId = config('reloadly.auth.id');
        $clientSecret = config('reloadly.auth.secret');

        if (empty($baseUri) || empty($clientId) || empty($clientSecret)) {
            throw new RuntimeException("Reloadly configs not set.");
        }

        $accessToken = $this->getAccessToken();

        $this->client = new Client([
            'base_uri'        => $baseUri,
            'allow_redirects' => false,
            'headers'         => [
                "Authorization" => "Bearer ${accessToken}",
                'Accept'        => 'application/com.reloadly.topups-v1+json',
            ],
        ]);
    }

    /**
     * @param string $method
     * @param string $path
     * @param array  $options
     * @return array
     */
    private function handleRequest(string $method, string $path, array $options = []): array
    {
        $result = [
            'status'      => 'fail',
            'status_code' => null,
            'body'        => null,
        ];

        try {
            $response = $this->client->request($method, $path, $options);

            $statusCode = $response->getStatusCode();
            $result['status_code'] = $statusCode;
            $result['body'] = json_decode($response->getBody()->getContents(), true);

            if (in_array($statusCode, [200, 201, 202, 203, 204, 205, 206, 207, 208, 226])) {
                $result['status'] = 'success';

                return $result;
            }

            return $result;
        } catch (GuzzleException | Exception $exception) {
            $statusCode = $exception->getCode();
            $result['status_code'] = $statusCode;
            $result['status'] = 'fail';
            $result['body'] = json_decode($exception->getMessage(), true);

            $this->getLogger()->error('GuzzleException', [
                'request' => [
                    'method'  => $method,
                    'path'    => $path,
                    'options' => $options,
                ],
                'result'  => $result,
            ]);

            return $result;
        }
    }

    /**
     * @return LoggerInterface
     */
    protected function getLogger(): LoggerInterface
    {
        return Log::channel('reloadly');
    }

    protected function generateCacheKey(string $identifier, string $supplementaryString = null): string
    {
        $primaryString = "RELOADLY_API_REQUEST_${identifier}";

        if ($supplementaryString) {
            $primaryString = "${primaryString}_${supplementaryString}";
        }

        return $primaryString;
    }
}
