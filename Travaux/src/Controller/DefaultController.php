<?php

namespace AcMarche\Travaux\Controller;

use AcMarche\Travaux\Repository\InterventionRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class DefaultController extends AbstractController
{
    public function __construct(private InterventionRepository $interventionRepository)
    {
    }

    #[Route(path: '/', name: 'homepage')]
    public function index(): RedirectResponse
    {
        if ($this->isGranted("ROLE_TRAVAUX")) {
            return $this->redirectToRoute('intervention', array(), '301');
        }

        return $this->redirectToRoute('app_login');
    }

    #[Route(path: '/grh', name: 'grh')]
    public function grh(): Response
    {
        $debut = \DateTime::createFromFormat('Y-m-d', '2022-05-01');
        $end = \DateTime::createFromFormat('Y-m-d', '2022-07-31');
        $interventions = $this->interventionRepository->findByDates($debut, $end);

        return $this->render('@AcMarcheTravaux/intervention/grh.html.twig', [
            'interventions' => $interventions,
        ]);
    }

    #[Route(path: '/documentation', name: 'documentation')]
    #[IsGranted('ROLE_TRAVAUX')]
    public function documentation(): Response
    {
        return $this->render('@AcMarcheTravaux/default/documentation.html.twig');
    }
}
