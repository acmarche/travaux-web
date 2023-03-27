<?php

namespace AcMarche\Travaux\Controller;

use AcMarche\Travaux\Entity\Etat;
use AcMarche\Travaux\Entity\Intervention;
use AcMarche\Travaux\Form\PlanningType;
use AcMarche\Travaux\Repository\CategorieRepository;
use AcMarche\Travaux\Repository\EtatRepository;
use AcMarche\Travaux\Repository\InterventionRepository;
use AcMarche\Travaux\Repository\PrioriteRepository;
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
        private EtatRepository $etatRepository,
        private PrioriteRepository $prioriteRepository,
        private CategorieRepository $categorieRepository,
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
            $data = $form->getData();
            $user = $this->getUser();
            $intervention->setUserAdd($user->getUserIdentifier());
            $intervention->setCurrentPlace('published');
            $etat = $this->etatRepository->find(1);//new
            $intervention->setEtat($etat);
            $priorite = $this->prioriteRepository->find(1);//normal
            $intervention->setPriorite($priorite);
            $category = $this->categorieRepository->find(3);//intervention
            $intervention->setCategorie($category);
            $intervention->isPlanning = true;

            $this->interventionRepository->persist($intervention);
            $this->interventionRepository->flush();

            return $this->redirectToRoute('planning_show', array('id' => $intervention->getId()));
        }

        return $this->render(
            '@AcMarcheTravaux/planning/new.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    #[Route(path: '/{id}/edit', name: 'planning_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Intervention $intervention): Response
    {
        $editForm = $this->createForm(PlanningType::class, $intervention);

        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->interventionRepository->flush();

            $this->addFlash('success', 'L\'employé a bien été modifié.');

            return $this->redirectToRoute('planning_show', array('id' => $intervention->getId()));
        }

        return $this->render(
            '@AcMarcheTravaux/planning/edit.html.twig',
            array(
                'intervention' => $intervention,
                'form' => $editForm->createView(),
            )
        );
    }

    #[Route(path: '/{id}', name: 'planning_show', methods: ['GET'])]
    public function show(Intervention $intervention): Response
    {
        return $this->render(
            '@AcMarcheTravaux/planning/show.html.twig',
            array(
                'intervention' => $intervention,
            )
        );
    }
}
