<?php

namespace AcMarche\Travaux\Search;

use AcMarche\Avaloir\Entity\Avaloir;
use Elastic\Elasticsearch\Exception\AuthenticationException;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Http\Promise\Promise;

class ElasticServer
{
    use ElasticClientTrait;


    /**
     * @return Elasticsearch|Promise
     * @throws ServerResponseException|AuthenticationException|ClientResponseException
     */
    public function info(): Elasticsearch|Promise
    {
        $this->connect();

        return $this->client->info();
    }

    /**
     * @return Elasticsearch|Promise
     * @throws ServerResponseException|AuthenticationException|ClientResponseException|MissingParameterException
     */
    public function reset(): Elasticsearch|Promise
    {
        $this->connect();
        try {
            $this->deleteIndex();
        } catch (\Exception $exception) {

        }

        return $this->createIndex();
    }

    /**
     * @return Elasticsearch|Promise
     * @throws ServerResponseException|AuthenticationException|ClientResponseException|MissingParameterException
     */
    public function createIndex(): Elasticsearch|Promise
    {
        $this->connect();
        $params = [
            'index' => $this->indexName,
            'body' => [
                "settings" =>
                    [
                        "index" => [
                            "number_of_shards" => 2,
                            "number_of_replicas" => 1,
                        ],
                    ],
                'mappings' => [
                    '_source' => [
                        'enabled' => true,
                    ],
                    'properties' => [
                        'location' => [
                            'type' => 'geo_point',
                        ],
                    ],
                ],
            ],
        ];

        return $this->client->indices()->create($params);
    }

    /**
     * https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/http-client.html
     * @throws AuthenticationException
     * @throws ClientResponseException
     * @throws ServerResponseException
     * @throws MissingParameterException
     */
    public function addDoc(Avaloir $avaloir): Elasticsearch|Promise
    {
        $data = $this->prepareAvaloir($avaloir);
        $params = [
            'index' => $this->indexName,
            'body' => $data,
        ];

        return $this->client->index($params);
    }

    /**
     * @return void
     * @throws ServerResponseException|AuthenticationException|ClientResponseException
     * @throws MissingParameterException
     */
    public function addAll(): void
    {
        $this->connect();
        foreach ($this->avaloirRepository->findAll() as $avaloir) {
            $this->addDoc($avaloir);
        }

        /*   $params = ['body' => []];

           for ($i = 1; $i <= 1234567; $i++) {
               $params['body'][] = [
                   'index' => [
                       '_index' => $this->indexName,
                       '_id' => $i,
                   ],
               ];

               $params['body'][] = [
                   'my_field' => 'my_value',
                   'second_field' => 'some more values',
               ];

               // Every 1000 documents stop and send the bulk request
               if ($i % 1000 == 0) {
                   $responses = $this->client->bulk($params);

                   // erase the old bulk request
                   $params = ['body' => []];

                   // unset the bulk response when you are done to save memory
                   unset($responses);
               }
           }

           // Send the last batch if it exists
           if (!empty($params['body'])) {
               $responses = $this->client->bulk($params);
           }*/
    }

    /**
     * @return Elasticsearch|Promise
     * @throws ServerResponseException|AuthenticationException|ClientResponseException|MissingParameterException
     */
    function deleteIndex(): Elasticsearch|Promise
    {
        $this->connect();
        $params = ['index' => $this->indexName];

        return $this->client->indices()->delete($params);
    }

    /**
     * @return Elasticsearch|Promise
     * @throws AuthenticationException
     * @throws ClientResponseException
     * @throws ServerResponseException
     */
    public function settings(): Elasticsearch|Promise
    {
        $this->connect();
        $params = [
            'index' => $this->indexName,
            'body' => [
                'settings' => [
                    'number_of_replicas' => 0,
                    'refresh_interval' => -1,
                ],
            ],
        ];

        return $this->client->indices()->putSettings($params);
    }

    private function prepareAvaloir(Avaloir $avaloir): array
    {
        return [
            'id' => $avaloir->getId(),
            'localite' => $avaloir->getLocalite(),
            'rue' => $avaloir->getRue(),
            'location' => [
                "type" => "Point",
                "coordinates" => [$avaloir->getLatitude(), $avaloir->getLongitude()],
            ],
            'description' => $avaloir->getDescription(),
        ];
    }

}