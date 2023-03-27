<?php

namespace AcMarche\Travaux\Controller;

use AcMarche\Travaux\Entity\Intervention;
use AcMarche\Travaux\Form\PlanningType;
use AcMarche\Travaux\Repository\InterventionRepository;
use AcMarche\Travaux\Spreadsheet\SpreadsheetDownloaderTrait;
use AcMarche\Travaux\Spreadsheet\XlsGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/planning')]
#[IsGranted('ROLE_TRAVAUX_ADMIN')]
class PlanningController extends AbstractController
{
    use SpreadsheetDownloaderTrait;

    public function __construct(
        private InterventionRepository $interventionRepository,
        private XlsGenerator $xlsGenerator
    ) {
    }

    #[Route(path: '/tt', name: 'planning_index')]
    public function index(): Response
    {

        return $this->render('@AcMarcheTravaux/planning/index.html.twig', [

        ]);
    }

    #[Route(path: '/new', name: 'planning_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_TRAVAUX_ADD')]
    public function new(Request $request): Response
    {

        $intervention = new Intervention();
        $form = $this->createForm(PlanningType::class, $intervention);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            return $this->redirectToRoute('intervention_show', array('id' => $intervention->getId()));
        }

        return $this->render(
            '@AcMarcheTravaux/planning/new.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    #[Route(path: '/{id}', name: 'planning_show', methods: ['GET'])]
    public function show(Document $document): Response
    {
        return $this->render(
            '@AcMarcheTravaux/document/show.html.twig',
            array(
                'entity' => $document,
            )
        );
    }
}
