<?php

namespace App\Libraries\Flutterwave\Utils;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;

class BaseUtil {

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $basePath;

    /**
     * BaseUtil constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        $baseUri = config('services.flutterwave.base_uri');
        $secretKey = config('services.flutterwave.secret_key');
        $version = config('services.flutterwave.version');

        if (empty($baseUri) || empty($secretKey) || empty($version)) {
            throw new Exception("Flutterwave config params not set.");
        }

        $this->basePath = "/v${version}/";

        $this->client = new Client([
            'base_uri'        => $baseUri,
            'allow_redirects' => false,
            'headers'         => [
                "Authorization" => "Bearer ${secretKey}",
            ],
        ]);
    }

    /**
     * @param string $method
     * @param string $path
     * @param array $options
     *
     * @return array
     */
    protected function handleRequest(string $method, string $path, array $options = [])
    {
        $result = [
            'status_code' => null,
            'status'      => null,
            'message'     => null,
        ];

        try {
            $response = $this->client->request($method, $this->getFullPath($path), $options);

            // Track response
            if ($path === 'transfers') {
                Log::channel('flutterwave_transfer_response')->info("Flutterwave API response", [
                    'status_code' => $response->getStatusCode(),
                    'status'      => $response->getReasonPhrase(),
                    'message'     => $response->getBody()->getContents(),
                ]);
            }

            $statusCode = $response->getStatusCode();
            $result['status_code'] = $statusCode;

            if (in_array($statusCode, [200, 201, 202, 203, 204, 205, 206, 207, 208, 226])) {
                $body = (string) $response->getBody();
                $responseData = json_decode($body, true);
                $responseData['status_code'] = $statusCode;

                return $responseData;
            }

            return $result;
        } catch (GuzzleException $exception) {
            $response = $exception->getResponse();
            $responseData = json_decode($response->getBody()->getContents(), true);
            $result = array_merge($result, $responseData, [
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
    }

    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return Log::channel('flutterwave');
    }

    /**
     * @param $result
     *
     * @return mixed|null
     */
    protected function getData($result)
    {

        if ( ! isset($result['status'])) {
            return null;
        }

        if ($result['status'] !== 'success') {
            return null;
        }

        if ( ! isset($result['data'])) {
            return null;
        }

        return $result['data'];
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function getFullPath(string $path)
    {
        return $this->basePath . ltrim($path, '/');
    }
}
