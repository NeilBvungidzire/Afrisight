<?php

namespace App\Services\SMSService;

use CMText\TextClient;
use CMText\TextClientStatusCodes;

class SMSService {

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $mobileNumber;

    public function __construct(string $message, string $mobileNumber)
    {
        $this->message = $message;
        $this->mobileNumber = $mobileNumber;
    }

    public function send(string $reference = null): bool
    {
        $client = $this->getClient();

        $result = $client->SendMessage($this->message, 'AfriSight', [$this->mobileNumber], $reference);

        return $result->statusCode === TextClientStatusCodes::OK;
    }

    private function getClient(): TextClient
    {
        return new TextClient(config('services.cm.api_key'));
    }
}
