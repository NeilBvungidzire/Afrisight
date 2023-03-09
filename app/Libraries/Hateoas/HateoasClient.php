<?php

namespace App\Libraries\Hateoas;

use Closure;
use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;

class HateoasClient {

    /**
     * @var bool|null
     */
    private $successful;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var string|null
     */
    private $errorMessage;

    /**
     * @var array
     */
    private $links;

    /**
     * @var array|null
     */
    private $responseContent;

    /**
     * @var HttpClient
     */
    private $client;

    /**
     * Client constructor.
     *
     * @param array $clientConfigs
     */
    protected function __construct(array $clientConfigs)
    {
        $this->initialize($clientConfigs);
    }

    /**
     * @param string $uri
     * @param string $method
     * @param array|null $headers
     * @param array $body
     *
     * @return $this
     * @throws GuzzleException
     */
    public function from(string $uri, string $method = 'GET', ?array $headers = null, array $body = [])
    {
        // Don't bother executive follow-up request if previously request in chain failed.
        if (isset($this->successful) && $this->successful === false) {
            return $this;
        }

        $headers = empty($headers) ? [] : $headers;
        $currentRequest = new Request($method, $uri, $headers, json_encode($body));
        $this->handleRequest($currentRequest);

        return $this;
    }

    /**
     * @param Request $currentRequest
     *
     * @throws GuzzleException
     */
    private function handleRequest(Request $currentRequest)
    {
        try {
            $response = $this->client->send($currentRequest);
            $statusCode = $response->getStatusCode();

            $this->statusCode = $statusCode;

            switch ($statusCode) {
                case 200: // OK
                case 201: // Created
                    $this->setStates($statusCode);
                    $this->responseContent = $this->parseResponseContent($response);
                    $this->links = (empty($this->responseContent['links'])) ? null : $this->responseContent['links'];
                    break;

                case 204: // No Content
                    $this->setStates($statusCode);
                    break;

                case 500:
                case 501:
                case 502:
                case 503:
                case 504:
                case 505:
                    $this->responseContent = null;
                    Log::error("Status: {$statusCode}");
                    break;

                case 404: // Not found
                case 422: // Unprocessed entity
                default:
                    $this->setStates($statusCode, $this->parseResponseContent($response));
                    Log::error("Status: {$statusCode}", $this->parseResponseContent($response));
            }

        } catch (ClientException|RequestException $exception) {
            // Don't need to track this exception. It's mostly caused by panelist/panels not found, which is going to
            // happen a lot, because of the chaining of methods.
            $this->setStates($exception->getCode(), $exception->getMessage());
            $this->responseContent = null;
        } catch (Exception $exception) {
            $this->setStates($exception->getCode(), $exception->getMessage());
            $this->responseContent = null;
            report($exception);
        }
    }

    /**
     * @param string $rel
     * @param string $method
     * @param array|null $headers
     * @param array $body
     *
     * @return $this
     * @throws GuzzleException
     */
    public function follow(string $rel, string $method = 'GET', ?array $headers = null, array $body = [])
    {
        if ( ! $link = $this->getLinkByRel($rel, $this->links)) {
            throw new Exception("Couldn't find the corresponding URI for rel {$rel}");
        }

        $headers = empty($headers) ? [] : $headers;
        $this->from($link, $method, $headers, $body);

        return $this;
    }

    /**
     * @return bool
     */
    public function hasFailed()
    {
        return ! (bool)$this->successful;
    }

    /**
     * @param Closure $callback
     *
     * @return $this
     */
    public function getResource(Closure $callback)
    {
        $callback($this->successful, $this->responseContent);

        return $this;
    }

    /**
     * @param string $rel
     *
     * @return null
     */
    public function getLinkByRel(string $rel)
    {
        if (empty($this->links)) {
            Log::error("Empty list of links.");

            return null;
        }

        foreach ($this->links as $link) {
            if ($link['rel'] === $rel) {
                return $link['href'];
            }
        }

        Log::error("No URL found for rel {$rel}");

        return null;
    }

    public function resetSuccessState()
    {
        $this->successful = null;

        return $this;
    }

    /**
     * @param int $statusCode
     * @param string|null $errorMessage
     */
    public function setStates(int $statusCode, ?string $errorMessage = null)
    {
        $this->statusCode = $statusCode;
        $this->errorMessage = $errorMessage;

        switch ($statusCode) {
            case 200:
            case 201:
                $this->successful = true;
                break;
            case 204:
                $this->errorMessage = null;
                $this->successful = true;
                $this->responseContent = null;
                break;

            default:
                $this->successful = false;
                $this->responseContent = null;
                $this->errorMessage = $errorMessage;
        }
    }

    /**
     * @param Response $response
     *
     * @return array|null
     */
    private function parseResponseContent(Response $response)
    {
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param array $configs
     */
    private function initializeClient(array $configs)
    {
        $this->client = new HttpClient($configs);
    }

    /**
     * @param array $clientConfigs
     */
    private function initialize(array $clientConfigs)
    {
        $this->initializeClient($clientConfigs);
    }
}
