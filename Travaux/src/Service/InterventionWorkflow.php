<?php
/**
 * Created by PhpStorm.
 * User: jfsenechal
 * Date: 6/12/16
 * Time: 13:31
 */

namespace AcMarche\Travaux\Service;

use AcMarche\Travaux\Entity\Intervention;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use Symfony\Component\Workflow\WorkflowInterface;

class InterventionWorkflow
{
    private ?WorkflowInterface $workflow = null;

    public function __construct(
        private Registry $workflowRegistry,
        private AuthorizationCheckerInterface $authorizationChecker,
        private TravauxUtils $travauxUtils
    ) {
    }

    /**
     * Lorsqu'on ajoute une nouvelle intervention
     */
    public function newIntervention(Intervention $intervention): Intervention
    {
        //si admin on passe toutes les etapes d'un coup
        if ($this->authorizationChecker->isGranted('ROLE_TRAVAUX_ADMIN')) {
            return $intervention->setCurrentPlace(WorkflowEnum::PUBLISHED->value);
        }

        /**
         * si auteur ajoute une intervention
         * demande une validation admin
         */
        if ($this->authorizationChecker->isGranted('ROLE_TRAVAUX_AUTEUR')) {
            return $intervention->setCurrentPlace(WorkflowEnum::ADMIN_CHECKING->value);
        }

        /**
         * si redacteur
         */
        if ($this->authorizationChecker->isGranted('ROLE_TRAVAUX_REDACTEUR')) {
            return $intervention->setCurrentPlace(WorkflowEnum::ADMIN_CHECKING->value);
        }

        /**
         * si contributeur
         */
        if ($this->authorizationChecker->isGranted('ROLE_TRAVAUX_CONTRIBUTEUR')) {
            return $intervention->setCurrentPlace(WorkflowEnum::AUTEUR_CHECKING->value);
        }

        return $intervention;
    }

    /**
     * L'auteur ou l'admin a accepté la demande
     * @return array|bool|mixed
     */
    public function applyAccepter(Intervention $intervention)
    {
        $this->workflow = $this->workflowRegistry->get($intervention);
        if ($this->workflow->can($intervention, 'auteur_accept')) {
            $transitions = ['auteur_accept'];
            foreach ($transitions as $transition) {
                $result = $this->applyTransition($intervention, $transition);
                if (isset($result['error'])) {
                    return $result;
                }
            }

            return true;
        }

        if ($this->workflow->can($intervention, 'publish')) {
            $transitions = ['publish'];
            foreach ($transitions as $transition) {
                $result = $this->applyTransition($intervention, $transition);
                if (isset($result['error'])) {
                    return $result;
                }
            }

            return true;
        }

        return ['error' => "Application impossible"];
    }

    /**
     * L'auteur ou l'admin a refusé la demande
     * @return array|bool|mixed
     */
    public function applyRefuser(Intervention $intervention)
    {
        $this->workflow = $this->workflowRegistry->get($intervention);

        if ($this->workflow->can($intervention, 'reject_from_auteur')) {
            $transition = 'reject_from_auteur';
            $result = $this->applyTransition($intervention, $transition);

            if (isset($result['error'])) {
                return $result;
            }

            return true;
        }

        if ($this->workflow->can($intervention, 'reject_from_admin')) {
            $transition = 'reject_from_admin';
            $result = $this->applyTransition($intervention, $transition);
            if (isset($result['error'])) {
                return $result;
            }

            return true;
        }

        return ['error' => "Application impossible"];
    }

    /**
     * l'admin a besoin d'infos complémentaires
     * @return array|bool|mixed
     */
    public function applyPlusInfo(Intervention $intervention)
    {
        $this->workflow = $this->workflowRegistry->get($intervention);
        $from = $this->getFromTransition($intervention);
        $role = false;

        $userAdd = $this->travauxUtils->getUser($intervention->getUserAdd());
        if ($userAdd) {
            $role = $this->travauxUtils->getRoleByEmail($userAdd->getEmail());
        }

        switch ($from) {
            case WorkflowEnum::AUTEUR_CHECKING->value:
                $transitions = ['info_back_contributeur'];
                break;
            case WorkflowEnum::ADMIN_CHECKING->value:
                $transitions = ['info_back_auteur'];
                if ($role === 'redacteur') {
                    $transitions = ['info_back_redacteur'];
                }
                break;
            default:
                $transitions = [];
                break;
        }

        if (count($transitions) == 0) {
            return true;
        }

        if ($this->workflow->can($intervention, $transitions[0])) {
            foreach ($transitions as $transition) {
                $result = $this->applyTransition($intervention, $transition);
                if (isset($result['error'])) {
                    return $result;
                }
            }

            return true;
        }

        return ['error' => "Application impossible"];
    }

    public function applyTransition(Intervention $intervention, $regle): array|bool
    {
        $this->workflow = $this->workflowRegistry->get($intervention);
        if ($this->workflow->can($intervention, $regle)) {
            try {
                $this->workflow->apply($intervention, $regle);

                return true;
            } catch (LogicException $e) {
                return ['error' => $e->getMessage()];
            }
        }

        return ['error' => "Application impossible"];
    }

    private function getFromTransition(Intervention $intervention): ?string
    {
        $this->workflow = $this->workflowRegistry->get($intervention);
        $from = null;
        $transitions = $this->workflow->getEnabledTransitions($intervention);

        if ($transitions !== []) {
            $transition = $transitions[0];
            if ($transition instanceof Transition) {
                $froms = $transition->getFroms();
                $from = $froms[0];
            }
        }

        return $from;
    }

    /**
     * @return array<WorkflowEnum>
     */
    public function getListWorkflow(): array
    {
        /**
         * Doit voir ceux non valider sinon ne voit pas ce qu'il a encode
         */
        if ($this->authorizationChecker->isGranted('ROLE_TRAVAUX_REDACTEUR')) {
            return [WorkflowEnum::PUBLISHED, WorkflowEnum::ADMIN_CHECKING];
        }

        /**
         * auteur doit voir demande des contributeurs et les siennes
         */
        if ($this->authorizationChecker->isGranted('ROLE_TRAVAUX_AUTEUR')) {
            return [WorkflowEnum::PUBLISHED, WorkflowEnum::AUTEUR_CHECKING, WorkflowEnum::ADMIN_CHECKING];
        }

        /**
         * contributeur doit voir ses demandes
         * les non valider aussi sinon ne voit pas ce qu'il a encode !
         * absence du cadre a notifier contrairement à l'admin et à l'auteur
         */
        if ($this->authorizationChecker->isGranted('ROLE_TRAVAUX_CONTRIBUTEUR')) {
            return [WorkflowEnum::PUBLISHED, WorkflowEnum::AUTEUR_CHECKING];
        }

        return [];

    }
}
