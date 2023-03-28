<?php
/**
 * Created by PhpStorm.
 * User: jfsenechal
 * Date: 13/12/16
 * Time: 9:38
 */

namespace AcMarche\Travaux\Service;

use AcMarche\Travaux\Entity\Categorie;
use AcMarche\Travaux\Entity\Intervention;
use AcMarche\Travaux\Entity\Security\User;
use AcMarche\Travaux\Entity\Suivi;
use AcMarche\Travaux\Repository\GroupRepository;
use AcMarche\Travaux\Repository\InterventionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class TravauxUtils
{
    /**
     * TravauxUtils constructor.
     * @param AuthorizationChecker $authorizationChecker
     * @param EntityManagerInterface $em
     */
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker,
        private InterventionRepository $interventionRepository,
        private EntityManagerInterface $em,
        private GroupRepository $groupRepository
    ) {
    }

    public function getInterventionsEnAttentes()
    {
        $places = null;

        if ($this->authorizationChecker->isGranted('ROLE_TRAVAUX_AUTEUR')) {
            $places = ['auteur_checking'];
        } elseif ($this->authorizationChecker->isGranted('ROLE_TRAVAUX_ADMIN')) {
            $places = ['admin_checking'];
        }

        if ($places) {
            return $this->interventionRepository->getInterventionsToValid(
                $places
            );
        }

        return [];
    }

    public function getUser(string $username): ?User
    {
        return $this->em->getRepository(User::class)->findOneBy(['username' => $username]);
    }

    public function getRoleByEmail(string $email): ?string
    {
        $auteurs = $this->getEmailsByGroup("TRAVAUX_AUTEUR");
        if (in_array($email, $auteurs)) {
            return 'auteur';
        }

        $redacteurs = $this->getEmailsByGroup("TRAVAUX_REDACTEUR");

        if (in_array($email, $redacteurs)) {
            return 'redacteur';
        }

        $contributeurs = $this->getEmailsByGroup("TRAVAUX_CONTRIBUTEUR");

        if (in_array($email, $contributeurs)) {
            return 'contributeur';
        }

        $admins = $this->getEmailsByGroup("TRAVAUX_ADMIN");

        if (in_array($email, $admins)) {
            return 'admin';
        }

        return null;
    }

    public function getEmailsByGroup(string $groupName): array
    {
        $group = $this->groupRepository->findOneBy(['name' => $groupName]);
        $destinataires = [];

        if ($group !== null) {
            $users = $group->getUsers();
            foreach ($users as $user) {
                $destinataires[] = $user->getEmail();
            }
        }

        return $destinataires;
    }

    public function getCategorieDefault(string $name): ?Categorie
    {
        return $this->em->getRepository(Categorie::class)->findOneBy(
            ['intitule' => $name]
        );
    }

    public function getConstraintsForUser(): array
    {
        $data = [];
        /**
         * auteur doit voir demande des contributeurs et les siennes
         */
        if ($this->authorizationChecker->isGranted('ROLE_TRAVAUX_AUTEUR')) {
            $data['role'] = 'AUTEUR';
            $data['withAValider'] = true;
        }

        /**
         * contributeur doit voir ses demandes
         * les non valider aussi sinon ne voit pas ce qu'il a encode !
         * absence du cadre a notifier contrairement à l'admin et à l'auteur
         */
        if ($this->authorizationChecker->isGranted('ROLE_TRAVAUX_CONTRIBUTEUR')) {
            $data['role'] = 'CONTRIBUTEUR';
            $data['withAValider'] = true;
        }

        /**
         * Doit voir ceux non valider sinon ne voit pas ce qu'il a encode
         */
        if ($this->authorizationChecker->isGranted('ROLE_TRAVAUX_REDACTEUR')) {
            $data['role'] = 'REDACTEUR';
            $data['withAValider'] = true;
        }

        return $data;
    }

    /**
     * @param $interventions Intervention[]
     */
    public function setLastSuivisForInterventions($interventions): void
    {
        foreach ($interventions as $intervention) {
            $suivis = $this->em->getRepository(Suivi::class)->getLastSuivi($intervention);
            if ($suivis) {
                $intervention->setLastSuivi($suivis);
            }
        }
    }
}
