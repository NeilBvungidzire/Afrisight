<?php

namespace App\Libraries\Elastic;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

class Elastic {

    /**
     * @var Client
     */
    private $client;

    public function __construct()
    {
        $this->client = ClientBuilder::create()
            ->setHosts(config('elastic.hosts'))
            ->setBasicAuthentication(config('elastic.basic_auth.username'), config('elastic.basic_auth.password'))
            ->build();
    }

    /**
     * @param string $index
     * @param int $id
     * @param array $data
     * @param string $version
     *
     * @return array|callable
     */
    public function addDocument(string $index, int $id, array $data, string $version)
    {
        return $this->client->index([
            'index' => "${index}_v${version}",
            'id'    => $id,
            'body'  => $data,
        ]);
    }

    /**
     * @param string $index
     * @param string $idKey
     * @param array $data
     * @param string $version
     *
     * @return array|callable
     */
    public function addDocuments(string $index, string $idKey, array $data, string $version)
    {
        $params = ['body' => []];
        foreach ($data as $item) {
            $params['body'][] = [
                'index' => [
                    '_index' => "${index}_v${version}",
                    '_id'    => $item[$idKey],
                ],
            ];

            $params['body'][] = $item;
        }

        return $this->client->bulk($params);
    }
}
