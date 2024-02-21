<?php

namespace AcMarche\Avaloir\Command;

use AcMarche\Avaloir\Repository\AvaloirRepository;
use AcMarche\Stock\Service\SerializeApi;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Cache\CacheInterface;

#[AsCommand(
    name: 'avaloir:cache',
    description: 'Generate cache'
)]
class CacheCommand extends Command
{
    public function __construct(
        private readonly AvaloirRepository $avaloirRepository,
        private readonly SerializeApi $serializeApi,
        private readonly CacheInterface $cache,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $date = "";
        $last = $this->avaloirRepository->getLastUpdatedAvaloir();

        if ($last) {
            $date = $last->getUpdatedAt()->format('Y-m-d H');
        }

        try {
            $this->cache->get('allAvaloirs-'.$date, function () {
                $this->serializeApi->serializeAvaloirs($this->avaloirRepository->getAll());
            });
        } catch (InvalidArgumentException $e) {
            $io->error($e->getMessage());
        }

        return Command::SUCCESS;
    }
}