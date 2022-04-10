<?php


namespace AcMarche\Travaux\Elastic;

use AcMarche\Avaloir\Entity\Avaloir;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ElasticServer
{
    public array $params;
    private ?Client $client = null;

    private string $indexName;

    /**
     * AcElasticServerManager constructor.
     * @param string $indexName
     * @throws Exception
     */
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $hosts = [
            $parameterBag->get('acmarche_travaux.elastic.host')
        ];

        try {
            $this->client = ClientBuilder::create()
                ->setHosts($hosts)
                ->build();
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }

        $this->indexName = $parameterBag->get('acmarche_travaux.elastic.index');

        $this->params = [
            'index' => $this->indexName,
        ];
        $this->parameterBag = $parameterBag;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function createIndex()
    {
        try {
            return $this->client->indices()->create(['index' => $this->indexName]);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function updateSettings()
    {
        $params = $this->readParams('settings');
        try {
            return $this->client->indices()->putSettings($params);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function updateMappings()
    {
        $params = $this->readParams('mappings');
        try {
            return $this->client->indices()->putMapping($params);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @throws Exception
     */
    public function deleteIndex(): array|bool
    {
        $params = [
            'index' => $this->indexName,
        ];

        $exist = $this->client->indices()->exists($params);

        if ($exist) {
            try {
                return $this->client->indices()->delete($params);
            } catch (Exception $e) {
                throw new Exception($e->getMessage(), $e->getCode(), $e);
            }
        }

        return true;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function close()
    {
        try {
            return $this->client->indices()->close(['index' => $this->indexName]);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function open()
    {
        try {
            return $this->client->indices()->open(['index' => $this->indexName]);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function readParams(string $fileName): array
    {
        return match ($fileName) {
            'settings' => json_decode(file_get_contents(__DIR__ . '/../../config/elastic/' . $fileName . '.json'), true, 512, JSON_THROW_ON_ERROR),
            'mappings' => json_decode(file_get_contents(__DIR__ . '/../../config/elastic/' . $fileName . '.json'), true, 512, JSON_THROW_ON_ERROR),
            default => [],
        };
    }

    /**
     *
     * @return array
     * @throws Exception
     */
    public function updateData(Avaloir $avaloir)
    {
        $data = [
            'index' => 'avaloir',
            'id' => $avaloir->getId(),
            'body' => [
                'id' => $avaloir->getId(),
                'location' => ['lat' => $avaloir->getLatitude(), 'lon' => $avaloir->getLongitude()],
                'description' => $avaloir->getDescription()
            ]
        ];

        try {
            return $this->formatResponse($this->client->index($data));
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * array(8) {
     * ["_index"]=>
     * string(7) "avaloir"
     * ["_type"]=>
     * string(4) "_doc"
     * ["_id"]=>
     * string(1) "1"
     * ["_version"]=>
     * int(1)
     * ["result"]=>
     * string(7) "created"
     * ["_shards"]=>
     * array(3) {
     * ["total"]=>
     * int(1)
     * ["successful"]=>
     * int(1)
     * ["failed"]=>
     * int(0)
     * }
     * ["_seq_no"]=>
     * int(0)
     * ["_primary_term"]=>
     * int(3)
     * }
     */
    protected function formatResponse(array $result): array
    {
        $data = ['result' => $result["result"]];

        if (isset($result['_shards'])) {
            $data['successful'] = $result['_shards']["successful"];
            $data['failed'] = $result['_shards']["failed"];
            return $data;
        }

        if (isset($result['result'])) {
            $data['successful'] = $result["successful"];
            $data['failed'] = $result["failed"];
            return $data;
        }

        return $data;
    }
}