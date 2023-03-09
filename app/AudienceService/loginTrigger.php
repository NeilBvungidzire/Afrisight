<?php
require_once "vendor/autoload.php";


use GuzzleHttp;

function LoginTrigger()
{
    $personID = $_POST["id"];
    $client = new Client([
        // Base URI is used with relative requests
        'base_uri' => 'http://164.92.155.59',
    ]);
    $last_active = new \DateTime();
    $response = $client->request('PATCH', '/api/v1/person/update', [
        'json' => [
            'personID' => $personID,
            'last_active' => $last_active
        ]
    ]);
    $body = $response->getBody();
    $responseBody = json_decode($body);
    //get status code using $response->getStatusCode();
    return $response->getStatusCode();
}
