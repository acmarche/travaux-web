<?php

namespace AcMarche\Avaloir\Command;

use Exception;
use AcMarche\Avaloir\Repository\AvaloirRepository;
use AcMarche\Travaux\Elastic\ElasticSearch;
use AcMarche\Travaux\Elastic\ElasticServer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
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
    public function __construct(
        private ElasticServer $elasticServer,
        private ElasticSearch $elasticSearch,
        private AvaloirRepository $avaloirRepository,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addOption('raz', null, InputOption::VALUE_NONE, 'Remise à zéro du moteur')
            ->addOption('reindex', null, InputOption::VALUE_NONE, 'Réindex tous les avaloirs')
            ->addArgument('latitude', InputArgument::OPTIONAL)
            ->addArgument('longitude', InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $raz = $input->getOption('raz');
        $reindex = $input->getOption('reindex');
        $reindex = $input->getOption('reindex');
        $latitude = $input->getArgument('latitude');
        $longitude = $input->getArgument('longitude');

        if ($raz) {
            try {
                $this->elasticServer->deleteIndex();
                $this->elasticServer->createIndex();
                $this->elasticServer->close();
                $this->elasticServer->updateSettings();
                $this->elasticServer->open();
                $this->elasticServer->updateMappings();
            } catch (Exception $e) {
                $io->error($e->getMessage());
            }
        }

        if ($latitude && $longitude) {
            $result = $this->elasticSearch->search("25m", $latitude, $longitude);
            print_r($result);
        }

        if ($reindex) {
            $this->updateAvaloirs();
        }

        return 0;
    }

    private function updateAvaloirs(): array
    {
        foreach ($this->avaloirRepository->findAll() as $avaloir) {
            $result = $this->elasticServer->updateData($avaloir);
            var_dump($result);
        }

        //$this->elasticServer->getClient()->indices()->refresh();
        return [];
    }
}
