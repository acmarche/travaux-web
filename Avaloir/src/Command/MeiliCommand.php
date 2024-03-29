<?php

namespace AcMarche\Avaloir\Command;

use AcMarche\Travaux\Search\MeiliServer;
use AcMarche\Travaux\Search\SearchMeili;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'avaloir:meili',
    description: 'Mise à jour du moteur de recherche'
)]
class MeiliCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private MeiliServer $meiliServer,
        private SearchMeili $meilisearch,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('key', "key", InputOption::VALUE_NONE, 'Create a key');
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

        $key = (bool)$input->getOption('key');
        $tasks = (bool)$input->getOption('tasks');
        $reset = (bool)$input->getOption('reset');
        $update = (bool)$input->getOption('update');

        if ($key) {
            dump($this->meiliServer->createKey());

            return Command::SUCCESS;
        }

        if ($tasks) {
            $this->tasks($output);

            return Command::SUCCESS;
        }

        if ($reset) {
            $result = $this->meiliServer->createIndex();
            dump($result);
            $result = $this->meiliServer->settings();
            dump($result);
        }

        if ($update) {
            $this->meiliServer->addAvaloirs();
        }

        if ($latitude && $longitude) {
            $this->io->writeln('search... lat: '.(float)$latitude.' lng: '.(float)$longitude.' distance '.$disance);
            $result = $this->meilisearch->searchGeo((float)$latitude, (float)$longitude, $disance);
            $this->displayResult($output, $result->getHits());

            return Command::SUCCESS;
        }

        return Command::SUCCESS;
    }

    private function tasks(OutputInterface $output): void
    {
        $this->meiliServer->init();
        $tasks = $this->meiliServer->client->getTasks();
        $data = [];
        foreach ($tasks->getResults() as $result) {
            $t = [$result['uid'], $result['status'], $result['type'], $result['startedAt']];
            $t['error'] = null;
            $t['url'] = null;
            if ($result['status'] == 'failed') {
                if (isset($result['error'])) {
                    $t['error'] = $result['error']['message'];
                    $t['link'] = $result['error']['link'];
                }
            }
            $data[] = $t;
        }
        $table = new Table($output);
        $table
            ->setHeaders(['Uid', 'status', 'Type', 'Date', 'Error', 'Url'])
            ->setRows($data);
        $table->render();
    }

    private function displayResult(OutputInterface $output, array $result): void
    {
        $data = [];
        foreach ($result as $hit) {
            $data[] = ['id' => $hit['id'], 'localite' => $hit['localite'], 'rue' => $hit['rue']];
        }
        $table = new Table($output);
        $table
            ->setHeaders(['Id', 'Localité', 'Rue'])
            ->setRows($data);
        $table->render();
    }


}
