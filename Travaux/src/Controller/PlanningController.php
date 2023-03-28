<?php

namespace AcMarche\Travaux\Controller;

use AcMarche\Travaux\Entity\DateEntity;
use AcMarche\Travaux\Entity\Intervention;
use AcMarche\Travaux\Form\PlanningType;
use AcMarche\Travaux\Repository\CategorieRepository;
use AcMarche\Travaux\Repository\DateRepository;
use AcMarche\Travaux\Repository\EtatRepository;
use AcMarche\Travaux\Repository\InterventionRepository;
use AcMarche\Travaux\Repository\PrioriteRepository;
use AcMarche\Travaux\Spreadsheet\SpreadsheetDownloaderTrait;
use AcMarche\Travaux\Spreadsheet\XlsGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
        private DateRepository $dateRepository,
        private XlsGenerator $xlsGenerator
    ) {
    }

    #[Route(path: '/tt', name: 'planning_index')]
    public function index(): Response
    {
        $interventions = $this->interventionRepository->findAllPlanning();

        return $this->render('@AcMarcheTravaux/planning/index.html.twig', [
            'interventions' => $interventions,
        ]);
    }

    #[Route(path: '/new/{date}', name: 'planning_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_TRAVAUX_ADD')]
    public function new(Request $request, string $date = null): Response
    {
        if (!$date) {
            $date = new \DateTime();
        } else {
            $date = \DateTime::createFromFormat('Y-m-d', $date);
        }

        $intervention = new Intervention();
        $dateType = new DateEntity($date);
        $intervention->dates->add($dateType);
        $form = $this->createForm(PlanningType::class, $intervention)
            ->add('saveAndAdd', SubmitType::class, [
                'label' => 'Ajouter et ajouter une autre',
                'attr' => ['class' => 'btn-success'],
            ]);

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

            return $form->get('saveAndAdd')->isClicked()
                ? $this->redirectToRoute('planning_new')
                : $this->redirectToRoute('planning_show', array('id' => $intervention->getId()));
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
        $form = $this->createForm(PlanningType::class, $intervention);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            foreach ($data->dates as $date) {
                if ($date->day == null) {
                    $this->dateRepository->remove($date);
                    $data->removeDate($date);

                }
            }

            $intervention->dates = $data->dates;
            $this->interventionRepository->flush();

            $this->addFlash('success', 'L\'employé a bien été modifié.');

            return $this->redirectToRoute('planning_show', array('id' => $intervention->getId()));
        }

        return $this->render(
            '@AcMarcheTravaux/planning/edit.html.twig',
            array(
                'intervention' => $intervention,
                'form' => $form->createView(),
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
