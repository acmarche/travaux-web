<?php

namespace AcMarche\Travaux\Command;

use AcMarche\Travaux\Repository\InterventionRepository;
use AcMarche\Travaux\Service\FileHelper;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'travaux:clean',
    description: 'cleaning'
)]
class CleanCommand extends Command
{
    public function __construct(
        private readonly InterventionRepository $interventionRepository,
        private readonly FileHelper $fileHelper,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $date = new \DateTime();
        $date->modify('-6 years');

        foreach ($this->interventionRepository->findOlderThan($date) as $intervention) {
            try {
                $this->fileHelper->deleteAllDocs($intervention);
                $this->interventionRepository->remove($intervention);
                $io->writeln($intervention->getUpdatedAt()->format('Y-m'));
                $this->interventionRepository->flush();
            } catch (Exception $e) {
                $io->error($e->getMessage());
            }
        }

        return Command::SUCCESS;

    }
}