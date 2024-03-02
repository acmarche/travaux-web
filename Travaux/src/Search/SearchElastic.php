<?php

namespace AcMarche\Travaux\Search;

use Elastic\Elasticsearch\Exception\AuthenticationException;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Http\Promise\Promise;

class SearchElastic
{
    use ElasticClientTrait;

    function searchOne(): Elasticsearch|Promise
    {
        $params = [
            'index' => $this->indexName,
            'id' => 'my_id',
        ];

        // Get doc at /my_index/_doc/my_id
        return $this->client->get($params);
    }

    /**
     * @throws AuthenticationException
     * @throws ClientResponseException
     * @throws ServerResponseException
     */
    function searchGeo(float $latitude, float $longitude, int $distance = 25): Elasticsearch|Promise
    {
        $this->connect();
        $params = [
            'index' => $this->indexName,
            'size' => 50,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            'match_all' => new \stdClass(),
                        ],
                        'filter' => [
                            "geo_distance" => [
                                "distance" => $distance."m",
                                "location" => [
                                    "lat" => $latitude,
                                    "lon" => $longitude,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        dump($params);

        return $this->client->search($params);
    }

    /**
     * @return Elasticsearch|Promise
     * @throws AuthenticationException
     * @throws ClientResponseException
     * @throws ServerResponseException
     */
    function search(): Elasticsearch|Promise
    {
        $this->connect();
        $params = [
            'index' => $this->indexName,
            'size' => 50,
            'body' => [
                'query' => [
                    'match' => [
                        'rue' => 'bois',
                    ],
                ],
            ],
        ];

        return $this->client->search($params);
    }
}