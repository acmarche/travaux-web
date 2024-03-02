<?php

namespace AcMarche\Travaux\Search;


use AcMarche\Avaloir\Repository\AvaloirRepository;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Exception\AuthenticationException;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Http\Promise\Promise;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

trait ElasticClientTrait
{
    public ?Client $client = null;
    private Promise|Elasticsearch $index;

    public function __construct(
        #[Autowire(env: 'ELASTIC_INDEX_NAME')] private string $indexName,
        #[Autowire(env: 'ELASTIC_USER')] private string $elasticUser,
        #[Autowire(env: 'ELASTIC_CRT_PATH')] private string $caCrtPath,
        private readonly AvaloirRepository $avaloirRepository,
        private readonly LoggerInterface $logger
    ) {

    }

    /**
     * @return void
     * @throws AuthenticationException
     */
    public function connect(): void
    {
        if (!$this->client) {
            $this->client = ClientBuilder::create()
                ->setHosts(['https://localhost:9200'])
                ->setBasicAuthentication('elastic', $this->elasticUser)
                ->setCABundle($this->caCrtPath)
                ->setLogger($this->logger)
                ->build();
        }
    }
}
