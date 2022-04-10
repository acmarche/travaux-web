<?php


namespace AcMarche\Travaux\Elastic;


class ElasticSearch
{
    public function __construct(private ElasticServer $elasticServer)
    {
    }

    /**
     * @param string $distance
     * @param float $latitude
     * @param float $longitude
     * https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-distance-feature-query.html
     */
    public function search(string $distance, $latitude, $longitude): array|callable
    {
        $json = '{
     "query": {
        "bool" : {
            "must" : {
                "match_all" : {}
            },
            "filter" : {
                "geo_distance" : {
                    "distance" : "' . $distance . '",
                    "location" : {
                        "lat" : ' . $latitude . ',
                        "lon" : ' . $longitude . '
                    }
                }
            }
        }
    }
 }';
        $params = [
            'index' => 'avaloir',
            'body' => $json
        ];

        return $this->elasticServer->getClient()->search($params);
    }

    private function test(): void
    {
        $params = [
            'index' => 'avaloir',
            'body' => [
                'query' => [
                    'match_all' => (object)[]
                ],
                'filter' => [
                    'geo_distance' => [
                        'distance' => '50km',
                        'location' => [
                            'lat' => 50,
                            'lon' => 5
                        ]
                    ]
                ]
            ]
        ];
    }
}
