<?php

namespace AcMarche\Avaloir\Command;

use AcMarche\Avaloir\Entity\DateNettoyage;
use AcMarche\Avaloir\Location\LocationUpdater;
use AcMarche\Avaloir\Repository\AvaloirRepository;
use AcMarche\Travaux\Search\MeiliServer;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'avaloir:finish',
    description: 'Finish new'
)]
class FinishCommand extends Command
{
    public function __construct(
        private readonly AvaloirRepository $avaloirRepository,
        private readonly MeiliServer $meiliServer,
        private readonly LocationUpdater $locationUpdater,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        foreach ($this->avaloirRepository->findAllNotFinished() as $avaloir) {
            try {
                $dateNettoyage = new DateNettoyage();
                $dateNettoyage->setAvaloir($avaloir);
                $dateNettoyage->setJour(new \DateTime());
                $this->avaloirRepository->persist($dateNettoyage);
                $this->avaloirRepository->flush();

            } catch (Exception $exception) {
                $io->error($exception->getMessage());
            }

            try {
                $this->locationUpdater->updateRueAndLocalite($avaloir);
            } catch (\Exception $exception) {
                $io->error($exception->getMessage());
            }

            $avaloir->finished = true;
            $this->avaloirRepository->flush();

            try {
                $this->meiliServer->addData($avaloir);
            } catch (Exception $e) {
                $io->error($e->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}