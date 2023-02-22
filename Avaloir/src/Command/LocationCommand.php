<?php

namespace AcMarche\Avaloir\Command;

use AcMarche\Avaloir\Location\LocationMath;
use AcMarche\Avaloir\Location\LocationReverseInterface;
use AcMarche\Avaloir\Location\LocationUpdater;
use AcMarche\Avaloir\Repository\AvaloirRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'avaloir:location',
    description: 'Reverse address'
)]
class LocationCommand extends Command
{
    private ?SymfonyStyle $io = null;

    public function __construct(
        private AvaloirRepository $avaloirRepository,
        private LocationReverseInterface $locationReverse,
        private LocationUpdater $locationUpdater,
        private LocationMath $locationMath,
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
        $this->io = new SymfonyStyle($input, $output);

        $avaloirs = $this->avaloirRepository->findAll();
        foreach ($avaloirs as $avaloir) {
            if ($avaloir->cos_longitude == 0) {
                $this->locationMath->calculate($avaloir);
            }
        }

        // $this->reverseAll();

        $this->avaloirRepository->flush();

        return Command::SUCCESS;
    }

    protected function testLocation(string $latitude, string $longitude): void
    {
        //$this->locationUpdater->updateRueAndLocalite($avaloir);
        //$this->testLocation($input->getArgument('latitude'), $input->getArgument('longitude'));
        $result = $this->locationReverse->reverse($latitude, $longitude);
        print_r(json_encode($result, JSON_THROW_ON_ERROR));
        $this->io->writeln($this->locationReverse->getRoad());
        $this->io->writeln($this->locationReverse->getLocality());
    }

    protected function reverseAll(): void
    {
        $avaloirs = $this->avaloirRepository->findWithOutStreet();
        foreach ($avaloirs as $avaloir) {
            $this->locationUpdater->updateRueAndLocalite($avaloir);
        }
    }
}
