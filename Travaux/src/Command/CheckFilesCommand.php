<?php

namespace AcMarche\Travaux\Command;

use AcMarche\Travaux\Repository\InterventionRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

#[AsCommand(
    name: 'actravaux:checkfiles', description: 'Verifie les pieces jointes'
)]
class CheckFilesCommand extends Command
{
    public function __construct(
        private MailerInterface $mailer,
        private InterventionRepository $interventionRepository,
        private ParameterBagInterface $parameterBag
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $root = $this->parameterBag->get('ac_marche_travaux.upload.directory');

        $interventions = $this->interventionRepository->findAll();
        foreach ($interventions as $intervention) {
            foreach ($intervention->getDocuments() as $document) {
                $path = $root.DIRECTORY_SEPARATOR.$intervention->getId();
                $fullPath = $path.DIRECTORY_SEPARATOR.$document->getFilename();

                if (!is_readable($fullPath)) {
                    $mail = (new TemplatedEmail())
                        ->subject('Travaux fichier manquant')
                        ->from("webmaster@marche.be")
                        ->to("webmaster@marche.be")
                        ->text("travaux fichier manquant: ".$fullPath);

                    try {
                        $this->mailer->send($mail);
                    } catch (TransportExceptionInterface $e) {
                        $output->writeln('error mail'.$e->getMessage());
                    }

                    $output->writeln((string)$fullPath);
                }
            }
        }

        return 0;
    }
}
