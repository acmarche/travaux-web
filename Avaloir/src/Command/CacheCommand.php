<?php

namespace AcMarche\Avaloir\Command;

use AcMarche\Avaloir\Repository\AvaloirRepository;
use AcMarche\Stock\Service\SerializeApi;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'avaloir:cache',
    description: 'Generate cache'
)]
class CacheCommand extends Command
{
    public function __construct(
        private AvaloirRepository $avaloirRepository,
        private SerializeApi $serializeApi
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('latitude')
            ->addArgument('longitude');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->serializeApi->serializeAvaloirs($this->avaloirRepository->getAll());

        return Command::SUCCESS;
    }
}
