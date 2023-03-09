<?php
require_once "vendor/autoload.php";

use GuzzleHttp;

function generalInfoUpdate($date_of_birth, $gender_code, $country_code, $personID)
{
    $date_of_birth = $_POST["date_of_birth"];
    $gender_code = $_POST["gender_code"];
    $country_code = $_POST["country_code"];
    $personID = $_POST["id"];

    $client = new Client([
        // Base URI is used with relative requests
        'base_uri' => 'http://164.92.155.59',
    ]);
    $response = $client->request('PATCH', '/api/v1/person/update', [
        'json' => [
            'personID' => $personID,
            'country_code' => $country_code,
            'gender_code' => $gender_code,
            'date_of_birth' => $date_of_birth,

        ]
    ]);
    $body = $response->getBody();
    $responseBody = json_decode($body);
    return $response->getStatusCode();
}
