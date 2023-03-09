<?php

namespace App\Libraries\APILayer;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;
use RuntimeException;

abstract class BaseUtils {

    /**
     * @var Client
     */
    private $client;

    /**
     * BaseUtil constructor.
     *
     * @throws RuntimeException
     */
    public function __construct() {
        $baseUri = config('services.api_layer.base_uri');
        $apiKey = config('services.api_layer.key');

        if (empty($baseUri) || empty($apiKey)) {
            throw new RuntimeException("APILayer config params not set.");
        }

        $this->client = new Client([
            'base_uri'        => $baseUri,
            'allow_redirects' => false,
            'headers'         => [
                "apiKey" => $apiKey,
            ],
        ]);
    }

    /**
     * @return string
     */
    abstract protected function getMarketplaceName(): string;

    /**
     * @param  array  $response
     * @return array|null
     */
    abstract public function getData(array $response): ?array;

    /**
     * @param  string  $method
     * @param  string  $path
     * @param  array  $options
     * @return array
     */
    protected function handleRequest(string $method, string $path, array $options = []): array {
        try {
            $response = $this->client->request($method, $this->getFullPath($path), $options);

            $statusCode = $response->getStatusCode();
            $result['status_code'] = $statusCode;

            if (in_array($statusCode, [200, 201, 202, 203, 204, 205, 206, 207, 208, 226])) {
                return json_decode($response->getBody()->getContents(), true);
            }
        } catch (GuzzleException $exception) {
            $result = array_merge(json_decode($exception->getResponse()->getBody(), true), [
                'status_code' => $exception->getCode(),
            ]);

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

        return [];
    }

    /**
     * @return LoggerInterface
     */
    protected function getLogger(): LoggerInterface {
        return Log::channel('api_layer');
    }

    /**
     * @param  string  $path
     *
     * @return string
     */
    private function getFullPath(string $path): string {
        return trim($this->getMarketplaceName(), '/') . '/' . trim($path, '/');
    }

    /**
     * @param  array  $requestParams
     * @return string
     */
    protected function generateCacheKey(array $requestParams): string {
        return 'API_LAYER_' . sha1(serialize($requestParams));
    }
}