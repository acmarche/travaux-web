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
use AcMarche\Travaux\Repository\CategorieRepository;
use AcMarche\Travaux\Repository\GroupRepository;
use AcMarche\Travaux\Repository\InterventionRepository;
use AcMarche\Travaux\Repository\SuiviRepository;
use AcMarche\Travaux\Repository\UserRepository;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class TravauxUtils
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker,
        private InterventionRepository $interventionRepository,
        private CategorieRepository $categorieRepository,
        private UserRepository $userRepository,
        private GroupRepository $groupRepository,
        private SuiviRepository $suiviRepository,
    ) {
    }

    /**
     * @return array<Intervention>
     */
    public function getInterventionsEnAttentes(): array
    {
        $places = null;

        if ($this->authorizationChecker->isGranted('ROLE_TRAVAUX_AUTEUR')) {
            $places = [WorkflowEnum::AUTEUR_CHECKING->value];
        } elseif ($this->authorizationChecker->isGranted('ROLE_TRAVAUX_ADMIN')) {
            $places = [WorkflowEnum::ADMIN_CHECKING->value];
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
        return $this->userRepository->findOneBy(['username' => $username]);
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
                if ($user->notification) {
                    $destinataires[] = $user->getEmail();
                }
            }
        }

        return $destinataires;
    }

    public function getDefaultCategory(string $name): ?Categorie
    {
        return $this->categorieRepository->findOneBy(['intitule' => $name]);
    }

    public function setRoleConstraint(): ?string
    {
        /**
         * auteur doit voir demande des contributeurs et les siennes
         */
        if ($this->authorizationChecker->isGranted('ROLE_TRAVAUX_AUTEUR')) {
            return 'AUTEUR';
        }

        /**
         * contributeur doit voir ses demandes
         * les non valider aussi sinon ne voit pas ce qu'il a encode !
         * absence du cadre a notifier contrairement à l'admin et à l'auteur
         */
        if ($this->authorizationChecker->isGranted('ROLE_TRAVAUX_CONTRIBUTEUR')) {
            return 'CONTRIBUTEUR';
        }

        /**
         * Doit voir ceux non valider sinon ne voit pas ce qu'il a encode
         */
        if ($this->authorizationChecker->isGranted('ROLE_TRAVAUX_REDACTEUR')) {
            return WorkflowEnum::REDACTEUR->value;
        }

        return null;
    }

    /**
     * @param $interventions Intervention[]
     */
    public function setLastSuivisForInterventions($interventions): void
    {
        foreach ($interventions as $intervention) {
            $suivis = $this->suiviRepository->getLastSuivi($intervention);
            if ($suivis) {
                $intervention->setLastSuivi($suivis);
            }
        }
    }
}
