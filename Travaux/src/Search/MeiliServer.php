<?php

namespace AcMarche\Travaux\Search;

use AcMarche\Avaloir\Entity\Avaloir;
use AcMarche\Avaloir\Repository\AvaloirRepository;
use Meilisearch\Contracts\DeleteTasksQuery;
use Meilisearch\Endpoints\Keys;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class MeiliServer
{
    use MeiliTrait;

    private string $primaryKey = 'id';
    private array $skips = [705];

    public function __construct(
        #[Autowire(env: 'MEILI_INDEX_NAME')]
        private string $indexName,
        #[Autowire(env: 'MEILI_MASTER_KEY')]
        private string $masterKey,
        private readonly AvaloirRepository $avaloirRepository
    ) {
    }

    /**
     *
     * @return array<'taskUid','indexUid','status','enqueuedAt'>
     */
    public function createIndex(): array
    {
        $this->init();
        $this->client->deleteTasks((new DeleteTasksQuery())->setStatuses(['failed', 'canceled', 'succeeded']));
        $this->client->deleteIndex($this->indexName);

        return $this->client->createIndex($this->indexName, ['primaryKey' => $this->primaryKey]);
    }

    /**
     * https://raw.githubusercontent.com/meilisearch/meilisearch/latest/config.toml
     * @return array
     */
    public function settings(): array
    {
        //don't return same fiches. Suppose you have numerous black jackets in different sizes in your costumes index
        //$this->client->index($this->indexName)->updateDistinctAttribute('societe');

        /*$this->client->index($this->indexName)->updateSearchableAttributes([
            'title',
            'overview',
            'genres',
        ]);*/

        return $this->client->index($this->indexName)->updateFilterableAttributes($this->facetFields);
    }

    /**
     * https://github.com/yooper/stop-words/blob/master/data/stop-words_french_1_fr.txt
     * @return void
     */
    public function stopWords(): void
    {
        $this->client->index($this->indexName)->updateStopWords(['the', 'of', 'to']);
    }

    public function addAvaloirs(): void
    {
        $documents = [];
        foreach ($this->avaloirRepository->findAll() as $avaloir) {
            $documents[] = $this->prepareAvaloir($avaloir);
        }
        $this->init();
        $index = $this->client->index($this->indexName);
        $index->addDocuments($documents, $this->primaryKey);
    }

    public function createKey(): Keys
    {
        $this->init();

        return $this->client->createKey([
            'description' => 'Bottin API key',
            'actions' => ['*'],
            'indexes' => [$this->indexName],
            'expiresAt' => '2042-04-02T00:42:42Z',
        ]);
    }

    public function addData(Avaloir $avaloir): void
    {
        $documents = [$this->prepareAvaloir($avaloir)];
        $this->init();
        $index = $this->client->index($this->indexName);
        $index->addDocuments($documents, $this->primaryKey);
    }

    private function prepareAvaloir(Avaloir $avaloir): array
    {
        return [
            'id' => $avaloir->getId(),
            'localite' => $avaloir->getLocalite(),
            '_geo' => ['lat' => $avaloir->latitude, 'lng' => $avaloir->longitude],
            'location' => ['lat' => $avaloir->getLatitude(), 'lon' => $avaloir->getLongitude()],
            'description' => $avaloir->getDescription(),
        ];
    }
}