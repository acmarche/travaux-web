<?php

namespace AcMarche\Travaux\Service;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use AcMarche\Travaux\Entity\Intervention;
use AcMarche\Travaux\Event\InterventionEvent;
use AcMarche\Travaux\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;

class Mailer
{
    public function __construct(private MailerInterface $mailer, private AuthorizationCheckerInterface $authorizationChecker, private Security $security, private UserRepository $userRepository, private TravauxUtils $travauxUtils)
    {
    }

    /**
     * Nouvelle intervention par un admin
     * Je previens par mail, tous les rédacteurs, les admins
     * @param $resultat
     * @throws TransportExceptionInterface
     */
    public function sendNewIntervention(InterventionEvent $event): void
    {
        $intervention = $event->getIntervention();
        $admins = $this->travauxUtils->getEmailsByGroup("TRAVAUX_ADMIN");
        $redacteurs = $this->travauxUtils->getEmailsByGroup("TRAVAUX_REDACTEUR");

        $currentUser = $this->security->getUser();

        $from = $currentUser->getEmail();
        $sujet = $intervention->getIntitule() . " a été ajoutée";
        $destinataires = array_unique(array_merge($admins, $redacteurs));

        $mail = (new TemplatedEmail())
            ->subject($sujet)
            ->from($from)
            ->textTemplate('@AcMarcheTravaux/mail/intervention.txt.twig')
            ->context(
                array(
                    'intervention' => $intervention,
                )
            );

        foreach ($destinataires as $destinataire) {
            $mail->addTo($destinataire);
        }

        $this->mailer->send($mail);
    }

    /**
     * Une nouvelle intervention est ajouté par contributeur, un auteur ou un redacteur
     * Contributeur => auteurs
     * Auteur => Admins
     * Redacteur => Admins
     *
     * @throws TransportExceptionInterface
     */
    public function sendAskValidation(InterventionEvent $event): void
    {
        $intervention = $event->getIntervention();
        $destinataires = [];

        if ($this->authorizationChecker->isGranted('ROLE_TRAVAUX_CONTRIBUTEUR')) {
            $destinataires = $this->travauxUtils->getEmailsByGroup("TRAVAUX_AUTEUR");
        }

        if (($this->authorizationChecker->isGranted('ROLE_TRAVAUX_AUTEUR')) || ($this->authorizationChecker->isGranted(
                'ROLE_TRAVAUX_REDACTEUR'
            ))) {
            $destinataires = $this->travauxUtils->getEmailsByGroup("TRAVAUX_ADMIN");
        }

        $currentUser = $this->security->getUser();
        $from = $currentUser->getEmail();

        $mail = (new TemplatedEmail())
            ->subject('Une demande d\'intervention à valider')
            ->from($from)
            ->textTemplate('@AcMarcheTravaux/mail/new_validation.txt.twig')
            ->context(
                array(
                    'intervention' => $intervention,
                )
            );

        foreach ($destinataires as $destinataire) {
            $mail->addTo($destinataire);
        }
        $this->mailer->send($mail);
    }

    /**
     * Si auteur accepte la demande on envoie une demande aux admins
     * @throws TransportExceptionInterface
     */
    public function sendAskValidationForAdmin(InterventionEvent $event): void
    {
        $intervention = $event->getIntervention();
        $destinataires = $this->travauxUtils->getEmailsByGroup("TRAVAUX_ADMIN");

        $currentUser = $this->security->getUser();

        $from = $currentUser->getEmail();

        $mail = (new TemplatedEmail())
            ->subject('Une demande d\'intervention à valider')
            ->from($from)
            ->textTemplate('@AcMarcheTravaux/mail/new_validation.txt.twig')
            ->context(
                array(
                    'intervention' => $intervention,
                )
            );
        foreach ($destinataires as $destinataire) {
            $mail->addTo($destinataire);
        }
        $this->mailer->send($mail);
    }

    /**
     * Envoie un mail lors du refus ou de l'acception d'une intervention
     * @param string $resultat
     * @throws TransportExceptionInterface
     */
    public function sendMailAcceptOrReject(InterventionEvent $event, $resultat): void
    {
        $intervention = $event->getIntervention();
        $destinataires = $this->getDestinatairesAccept($intervention);
        $dateExecution = $event->getDateExecution();

        if ($resultat == 'refusée') {
            $destinataires = $this->getDestinatairesReject($intervention);
        }

        $message = $event->getMessage();
        $currentUser = $this->security->getUser();

        $from = $currentUser->getEmail();
        $sujet = $intervention->getIntitule() . " a été " . $resultat;

        $mail = (new TemplatedEmail())
            ->subject($sujet)
            ->from($from)
            ->textTemplate('@AcMarcheTravaux/mail/result_validation.txt.twig')
            ->context(
                array(
                    'result' => $resultat,
                    'intervention' => $intervention,
                    'message' => $message,
                    'dateExecution' => $dateExecution,
                )
            );
        foreach ($destinataires as $destinataire) {
            $mail->addTo($destinataire);
        }
        $this->mailer->send($mail);
    }


    /**
     * Admin demande plus d'info à un contributeur
     * On envoie un mail a tous les auteurs
     * et au contributeur
     * @throws TransportExceptionInterface
     */
    public function sendMailPlusInfoContributeur(InterventionEvent $event): void
    {
        $intervention = $event->getIntervention();
        $message = $event->getMessage();

        $destinataires = $this->travauxUtils->getEmailsByGroup("TRAVAUX_AUTEUR");
        $userAdd = $this->userRepository->findOneBy(['username' => $intervention->getUserAdd()]);
        if ($userAdd !== null) {
            $destinataires[] = $userAdd->getEmail();
        }

        $currentUser = $this->security->getUser();

        $from = $currentUser->getEmail();

        $mail = (new TemplatedEmail())
            ->subject("Besoin d'informations pour une intervention")
            ->from($from)
            ->textTemplate('@AcMarcheTravaux/mail/plus_info_contributeur.txt.twig')
            ->context(
                array(
                    'intervention' => $intervention,
                    'message' => $message,
                )
            );
        foreach ($destinataires as $destinataire) {
            $mail->addTo($destinataire);
        }
        $this->mailer->send($mail);
    }


    /**
     * Admin demande plus d'info à un auteur
     * On envoie un mail a tous les auteurs
     * @throws TransportExceptionInterface
     */
    public function sendMailPlusInfoAuteur(InterventionEvent $event): void
    {
        $intervention = $event->getIntervention();
        $message = $event->getMessage();

        $destinataires = $this->travauxUtils->getEmailsByGroup("TRAVAUX_AUTEUR");

        $currentUser = $this->security->getUser();

        $from = $currentUser->getEmail();

        $mail = (new TemplatedEmail())
            ->subject("Besoin d'informations pour une intervention")
            ->from($from)
            ->textTemplate('@AcMarcheTravaux/mail/plus_info_auteur.txt.twig')
            ->context(
                array(
                    'intervention' => $intervention,
                    'message' => $message,
                )
            );
        foreach ($destinataires as $destinataire) {
            $mail->addTo($destinataire);
        }
        $this->mailer->send($mail);
    }

    /**
     * L'admin a besoin d'info
     * On envoie a celui qui a fait la demande
     *
     * @throws TransportExceptionInterface
     */
    public function sendMailPlusInfoRedacteur(InterventionEvent $event): void
    {
        $intervention = $event->getIntervention();
        $message = $event->getMessage();

        $destinataires = [];
        $userAdd = $this->userRepository->findOneBy(['username' => $intervention->getUserAdd()]);
        if ($userAdd !== null) {
            $destinataires[] = $userAdd->getEmail();
        }

        $currentUser = $this->security->getUser();

        $from = $currentUser->getEmail();

        $mail = (new TemplatedEmail())
            ->subject("Besoin d'informations pour une intervention")
            ->from($from)
            ->textTemplate('@AcMarcheTravaux/mail/plus_info_redacteur.txt.twig')
            ->context(
                array(
                    'intervention' => $intervention,
                    'message' => $message,
                )
            );
        foreach ($destinataires as $destinataire) {
            $mail->addTo($destinataire);
        }
        $this->mailer->send($mail);
    }

    /**
     * Lorsqu'un admin archive une intervention
     * J'envoie un mail aux admins et au rédacteurs
     * @throws TransportExceptionInterface
     */
    public function sendMailArchive(InterventionEvent $event): void
    {
        $intervention = $event->getIntervention();
        $redacteurs = $this->travauxUtils->getEmailsByGroup("TRAVAUX_REDACTEUR");
        $admins = $this->travauxUtils->getEmailsByGroup("TRAVAUX_ADMIN");

        $destinataires = array_unique(array_merge($admins, $redacteurs));

        $currentUser = $this->security->getUser();

        $from = $currentUser->getEmail();

        $mail = (new TemplatedEmail())
            ->subject('Archivage de : ' . $intervention->getIntitule())
            ->from($from)
            ->textTemplate('@AcMarcheTravaux/mail/archive.txt.twig')
            ->context(
                array(
                    'intervention' => $intervention,
                )
            );
        foreach ($destinataires as $destinataire) {
            $mail->addTo($destinataire);
        }
        $this->mailer->send($mail);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendMailSuivi(InterventionEvent $event): void
    {
        $intervention = $event->getIntervention();
        $suivi = $event->getSuivi();

        $redacteurs = $this->travauxUtils->getEmailsByGroup("TRAVAUX_REDACTEUR");
        $admins = $this->travauxUtils->getEmailsByGroup("TRAVAUX_ADMIN");

        $destinataires = array_unique(array_merge($admins, $redacteurs));

        $currentUser = $this->security->getUser();

        $from = $currentUser->getEmail();

        $mail = (new TemplatedEmail())
            ->subject('Ajout d\'un suivi pour : ' . $intervention->getIntitule())
            ->from($from)
            ->textTemplate('@AcMarcheTravaux/mail/suivis.txt.twig')
            ->context(
                array(
                    'intervention' => $intervention,
                    'suivi' => $suivi,
                )
            );
        foreach ($destinataires as $destinataire) {
            $mail->addTo($destinataire);
        }
        $this->mailer->send($mail);
    }

    /**
     * Si accepter par un admin
     * Je previens le groupe admin et redacteur ainsi que celui qui a a joute
     *      Si ajoute par contributeur
     *          Je previens par mail, en plus les auteurs
     *      Si auteur
     *          Je previens par mail, en plus ?
     *      Si redacteur
     *          Je previens par mail, en plus ?
     * Si accepter par auteur
     * Je previens par mail celui qui a ajoute et les auteurs
     */
    private function getDestinatairesAccept(Intervention $intervention): array
    {
        $userAdd = $this->userRepository->findOneBy(['username' => $intervention->getUserAdd()]);
        $admins = $redacteurs = $auteurs = [];

        if ($this->authorizationChecker->isGranted('ROLE_TRAVAUX_ADMIN')) {
            $admins = $this->travauxUtils->getEmailsByGroup("TRAVAUX_ADMIN");
            $redacteurs = $this->travauxUtils->getEmailsByGroup("TRAVAUX_REDACTEUR");
            if ($userAdd !== null) {
                $role = $this->travauxUtils->getRoleByEmail($userAdd->getEmail());
                if ($role == 'contributeur') {
                    $auteurs = $this->travauxUtils->getEmailsByGroup("TRAVAUX_AUTEUR");
                }
            }
        }

        if ($this->authorizationChecker->isGranted('ROLE_TRAVAUX_AUTEUR')) {
            $auteurs = $this->travauxUtils->getEmailsByGroup("TRAVAUX_AUTEUR");
        }

        $destinataires = array_unique(array_merge($admins, $auteurs, $redacteurs));

        if ($userAdd !== null) {
            $destinataires[] = $userAdd->getEmail();
        }

        return $destinataires;
    }

    /**
     * Si refuser par un ADMIN
     *      Si ajoute par contributeur
     *      Je previens par mail les admins, auteurs et celui qui a ajouté
     *      Si ajoute par un redacteur
     *      Je previens par mail les auteurs et celui qui a ajouté
     * Si refuser par un AUTEUR
     *      Je previens par mail les auteurs et celui qui a ajouté
     */
    private function getDestinatairesReject(Intervention $intervention): array
    {
        $userAdd = $this->userRepository->findOneBy(['username' => $intervention->getUserAdd()]);
        $admins = $redacteurs = [];

        if ($this->authorizationChecker->isGranted('ROLE_TRAVAUX_ADMIN')) {
            $admins = $this->travauxUtils->getEmailsByGroup("TRAVAUX_ADMIN");

            if ($userAdd !== null) {
                $role = $this->travauxUtils->getRoleByEmail($userAdd->getEmail());
                if ($role != 'contributeur') {
                    $redacteurs = $this->travauxUtils->getEmailsByGroup("TRAVAUX_REDACTEUR");
                }
            }
        }

        $auteurs = $this->travauxUtils->getEmailsByGroup("TRAVAUX_AUTEUR");

        $destinataires = array_unique(array_merge($admins, $auteurs, $redacteurs));

        if ($userAdd !== null) {
            $destinataires[] = $userAdd->getEmail();
        }

        return $destinataires;
    }
}
