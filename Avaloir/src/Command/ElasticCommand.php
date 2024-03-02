<?php

namespace AcMarche\Avaloir\Command;

use AcMarche\Travaux\Search\ElasticServer;
use AcMarche\Travaux\Search\SearchElastic;
use Elastic\Elasticsearch\Exception\AuthenticationException;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'avaloir:elastic',
    description: 'Mise à jour du moteur de recherche'
)]
class ElasticCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private ElasticServer $elasticServer,
        private SearchElastic $searchElastic,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('info', "info", InputOption::VALUE_NONE, 'Information');
        $this->addOption('tasks', "tasks", InputOption::VALUE_NONE, 'Display tasks');
        $this->addOption('reset', "reset", InputOption::VALUE_NONE, 'Search engine reset');
        $this->addOption('update', "update", InputOption::VALUE_NONE, 'Update data');
        $this->addArgument('latitude', InputArgument::OPTIONAL);
        $this->addArgument('longitude', InputArgument::OPTIONAL);
        $this->addArgument('distance', InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $latitude = $input->getArgument('latitude');
        $longitude = $input->getArgument('longitude');
        $disance = (int)$input->getArgument('distance');

        $info = (bool)$input->getOption('info');
        $tasks = (bool)$input->getOption('tasks');
        $reset = (bool)$input->getOption('reset');
        $update = (bool)$input->getOption('update');

        if ($info) {
            try {
                //  dump($this->elasticServer->info());
                $mapping = $this->elasticServer->getMapping();
                dump($mapping->asObject());
            } catch (ClientResponseException|ServerResponseException $e) {
                $this->io->error($e->getMessage());
            }

            return Command::SUCCESS;
        }

        if ($reset) {
            try {
                $result = $this->elasticServer->reset();
                dump($result);
            } catch (ClientResponseException|ServerResponseException|MissingParameterException|AuthenticationException $e) {
                $this->io->error($e->getMessage());
            }
        }

        if ($update) {
            try {
                $this->elasticServer->addAll();
            } catch (AuthenticationException|ClientResponseException|MissingParameterException|ServerResponseException $e) {
                $this->io->error($e->getMessage());
            }
        }

        if ($latitude && $longitude) {
            $this->io->writeln('search... lat: '.(float)$latitude.' lng: '.(float)$longitude.' distance '.$disance);
            try {
                $result = $this->searchElastic->searchGeo((float)$latitude, (float)$longitude, $disance);
                $count = $result->asObject()->hits->total->value;
                $this->displayResult($output, $result->asObject()->hits->hits);
            } catch (AuthenticationException|ClientResponseException|ServerResponseException $e) {
                $this->io->error($e->getMessage());
            }

            return Command::SUCCESS;
        }

        return Command::SUCCESS;
    }

    private function displayResult(OutputInterface $output, array $result): void
    {
        $data = [];
        foreach ($result as $hit) {
            $source = $hit->_source;
            $data[] = ['id' => $source->id, 'localite' => $source->localite, 'rue' => $source->rue];
        }
        $table = new Table($output);
        $table
            ->setHeaders(['Id', 'Localité', 'Rue'])
            ->setRows($data);
        $table->render();
    }

}
