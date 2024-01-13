<?php

namespace AcMarche\Travaux\Controller;

use AcMarche\Travaux\Repository\InterventionRepository;
use AcMarche\Travaux\Service\TravauxUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Afficher la bare de notification pour les interventions
 * en attentes pour l'auteur ou l'administrateur
 * Appeler depuis le fichier base.html
 */
#[Route(path: '/notification')]
#[IsGranted('ROLE_TRAVAUX')]
class NotificationController extends AbstractController
{
    public function __construct(
        private InterventionRepository $interventionRepository,
        private TravauxUtils $travauxUtils
    ) {
    }

    public function index(): Response
    {
        $interventions = $this->travauxUtils->getInterventionsEnAttentes();
        $reportees = $this->interventionRepository->getInterventionsReportees();

        return $this->render('@AcMarcheTravaux/notification/index.html.twig', array(
            'interventions' => $interventions,
            'reportees' => $reportees,
        ));
    }

    #[Route(path: '/reporte', name: 'intervention_reporte', methods: ['GET'])]
    public function reporte(): Response
    {
        $reportees = $this->interventionRepository->getInterventionsReportees();

        return $this->render('@AcMarcheTravaux/notification/reporte.html.twig', array(
            'interventions' => $reportees,
        ));
    }
}
