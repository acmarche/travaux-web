<?php

namespace AcMarche\Travaux\Controller;

use AcMarche\Travaux\Entity\Intervention;
use AcMarche\Travaux\Form\PlanningType;
use AcMarche\Travaux\Planning\TreatmentDates;
use AcMarche\Travaux\Repository\CategorieRepository;
use AcMarche\Travaux\Repository\EtatRepository;
use AcMarche\Travaux\Repository\InterventionRepository;
use AcMarche\Travaux\Repository\PrioriteRepository;
use AcMarche\Travaux\Spreadsheet\SpreadsheetDownloaderTrait;
use AcMarche\Travaux\Spreadsheet\XlsGenerator;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Doctrine\Common\Collections\ArrayCollection;
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
        private XlsGenerator $xlsGenerator
    ) {
    }

    #[Route(path: '/tt', name: 'planning_index')]
    public function index(): Response
    {
        $interventions = $this->interventionRepository->findAllPlanning();
        $currentMonth = Carbon::now()->toImmutable();
        $days = CarbonPeriod::create($currentMonth->firstOfMonth(), $currentMonth->endOfMonth());
        $data = [];
        foreach ($days as $day) {

            //$data[$day->day] = $this->interventionRepository->findPlanningByDay($dates);
        }

        dump($data);

        return $this->render('@AcMarcheTravaux/planning/index.html.twig', [
            'interventions' => $interventions,
            'curentMonth' => $currentMonth->monthName,
            'days' => $days,
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
        $intervention->datesCollection = new ArrayCollection();
        $intervention->datesCollection->add($date);

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

            TreatmentDates::setDatesFromCollection($intervention, $data);

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

    #[Route(path: '/{id}', name: 'planning_show', methods: ['GET'])]
    public function show(Intervention $intervention): Response
    {
        return $this->render(
            '@AcMarcheTravaux/planning/show.html.twig',
            [
                'intervention' => $intervention,
            ]
        );
    }

    #[Route(path: '/{id}/edit', name: 'planning_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Intervention $intervention): Response
    {
        TreatmentDates::setDatesCollectionFromDates($intervention);

        $form = $this->createForm(PlanningType::class, $intervention);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            TreatmentDates::setDatesFromCollection($intervention, $data);
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

}
