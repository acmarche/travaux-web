<?php

namespace AcMarche\Avaloir\Command;

use DateTime;
use AcMarche\Avaloir\Repository\AvaloirRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

class RappelCommand extends Command
{
    protected static $defaultName = 'avaloir:rappel';
    private ?string $name;

    public function __construct(
        private MailerInterface $mailer,
        private AvaloirRepository $avaloirRepository,
        private ParameterBagInterface $parameterBag,
        string $name = null
    ) {
        parent::__construct($name);
        $this->name = $name;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Lance les rappels');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $destinataire = $this->parameterBag->get('ac_marche_avaloir_destinataire');

        $avaloirs = $this->avaloirRepository->findBy(['date_rappel' => new DateTime()]);

        if ($avaloirs !== []) {
            $mail = (new TemplatedEmail())
                ->subject("Rappel avaloir")
                ->from($destinataire)
                ->to($destinataire)
                ->textTemplate('@AcMarcheAvaloir/mail/rappel.txt.twig')
                ->context(
                    array(
                        'avaloirs' => $avaloirs,
                    )
                );

            try {
                $this->mailer->send($mail);
            } catch (TransportExceptionInterface $e) {
                $output->writeln('error mail'.$e->getMessage());
            }
        }

        return 0;
    }
}
