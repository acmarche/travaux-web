<?php

namespace AcMarche\Avaloir\Command;

use AcMarche\Avaloir\Location\LocationReverseInterface;
use AcMarche\Avaloir\Location\LocationUpdater;
use AcMarche\Avaloir\MailerAvaloir;
use AcMarche\Avaloir\Repository\AvaloirRepository;
use AcMarche\Avaloir\Repository\RueRepository;
use AcMarche\Stock\Service\SerializeApi;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class LocationCommand extends Command
{
    protected static $defaultName = 'avaloir:location';
    private SerializeApi $serializeApi;
    private ?SymfonyStyle $io = null;
    private RueRepository $rueRepository;
    private MailerAvaloir $mailerAvaloir;

    public function __construct(
        private AvaloirRepository $avaloirRepository,
        private LocationReverseInterface $locationReverse,
        private LocationUpdater $locationUpdater,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Reverse address')
            ->addArgument('latitude')
            ->addArgument('longitude');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

      //   $avaloir = $this->avaloirRepository->find(2);
      //   $this->locationUpdater->updateRueAndLocalite($avaloir);

    //    $this->testLocation($input->getArgument('latitude'), $input->getArgument('longitude'));

        $this->reverseAll();

        return 0;
    }

    protected function testLocation(string $latitude, string $longitude): void
    {
        $result = $this->locationReverse->reverse($latitude, $longitude);
        print_r(json_encode($result, JSON_THROW_ON_ERROR));
        $this->io->writeln($this->locationReverse->getRoad());
        $this->io->writeln($this->locationReverse->getLocality());
    }

    protected function reverseAll(): void
    {
        $avaloirs = $this->avaloirRepository->findAll();

        foreach ($avaloirs as $avaloir) {
            //$this->serializeApi->serializeAvaloir($avaloir);
            //  if (!$avaloir->getRue()) {
            $this->locationUpdater->updateRueAndLocalite($avaloir);
            // }
        }
    }
}
