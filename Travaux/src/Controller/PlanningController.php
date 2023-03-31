<?php

namespace AcMarche\Travaux\Controller;

use AcMarche\Travaux\Entity\CategoryPlanning;
use AcMarche\Travaux\Entity\InterventionPlanning;
use AcMarche\Travaux\Form\PlanningType;
use AcMarche\Travaux\Planning\DateProvider;
use AcMarche\Travaux\Planning\TreatmentDates;
use AcMarche\Travaux\Repository\CategorieRepository;
use AcMarche\Travaux\Repository\CategoryPlanningRepository;
use AcMarche\Travaux\Repository\EtatRepository;
use AcMarche\Travaux\Repository\InterventionPlanningRepository;
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
#[IsGranted('ROLE_TRAVAUX_PLANNING')]
class PlanningController extends AbstractController
{
    use SpreadsheetDownloaderTrait;

    public function __construct(
        private InterventionPlanningRepository $interventionPlanningRepository,
        private CategoryPlanningRepository $categoryPlanningRepository,
        private EtatRepository $etatRepository,
        private PrioriteRepository $prioriteRepository,
        private CategorieRepository $categorieRepository,
        private DateProvider $dateProvider,
        private XlsGenerator $xlsGenerator
    ) {
    }

    #[Route(path: '/tt/{monthyear}/{categoryPlanning}', name: 'planning_index')]
    public function index(string $monthyear = null, CategoryPlanning $categoryPlanning = null): Response
    {
        $dateSelected = Carbon::now()->toImmutable();
        if ($monthyear) {
            $dateSelected = Carbon::createFromFormat('Y-m-d', $monthyear.'-01')->toImmutable();
        }

        $interventions = $this->interventionPlanningRepository->findByCategory($categoryPlanning);
        $days = CarbonPeriod::create($dateSelected->firstOfMonth(), $dateSelected->endOfMonth());
        $data = [];
        foreach ($days as $day) {
            $data[$day->day] = $this->interventionPlanningRepository->findPlanningByDayAndCategory(
                $day,
                $categoryPlanning
            );
        }

        $next = $dateSelected->addMonth();
        $previous = $dateSelected->subMonth();
        $today = Carbon::today();

        $weeks = $this->dateProvider->weeksOfMonth($dateSelected);

        return $this->render('@AcMarcheTravaux/planning/index.html.twig', [
            'interventions' => $interventions,
            'dateSelected' => $dateSelected,
            'next' => $next,
            'today' => $today,
            'weekdays' => $this->dateProvider->weekDaysName(),
            'previous' => $previous,
            'days' => $days,
            'weeks' => $weeks,
            'data' => $data,
            'categorySelected' => $categoryPlanning,
            'categories' => $this->categoryPlanningRepository->findAllOrdered(),
        ]);
    }

    #[Route(path: '/new/{date}', name: 'planning_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_TRAVAUX_ADD')]
    public function new(Request $request, string $date = null): Response
    {
        if (!$date) {
            $dateSelected = new \DateTime();
        } else {
            $dateSelected = \DateTime::createFromFormat('Y-m-d', $date);
        }

        $intervention = new InterventionPlanning();
        $intervention->datesCollection = new ArrayCollection();
        $intervention->datesCollection->add($dateSelected);

        $form = $this->createForm(PlanningType::class, $intervention)
            ->add('saveAndAdd', SubmitType::class, [
                'label' => 'Ajouter et ajouter une autre',
                'attr' => ['class' => 'btn-success'],
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $user = $this->getUser();
            $intervention->user_add = $user->getUserIdentifier();

            TreatmentDates::setDatesFromCollection($intervention, $data);

            $this->interventionPlanningRepository->persist($intervention);
            $this->interventionPlanningRepository->flush();

            return $form->get('saveAndAdd')->isClicked()
                ? $this->redirectToRoute('planning_new')
                : $this->redirectToRoute('planning_show', array('id' => $intervention->getId()));
        }

        return $this->render(
            '@AcMarcheTravaux/planning/new.html.twig',
            array(
                'form' => $form->createView(),
                'dateSelected' => $dateSelected,
            )
        );
    }

    #[Route(path: '/{id}', name: 'planning_show', methods: ['GET'])]
    public function show(InterventionPlanning $intervention): Response
    {
        return $this->render(
            '@AcMarcheTravaux/planning/show.html.twig',
            [
                'intervention' => $intervention,
            ]
        );
    }

    #[Route(path: '/{id}/edit', name: 'planning_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, InterventionPlanning $intervention): Response
    {
        TreatmentDates::setDatesCollectionFromDates($intervention);

        $form = $this->createForm(PlanningType::class, $intervention);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            TreatmentDates::setDatesFromCollection($intervention, $data);
            $this->interventionPlanningRepository->flush();

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
