<?php

namespace AcMarche\Travaux\Controller;

use AcMarche\Travaux\Absence\AbsenceUtils;
use AcMarche\Travaux\Entity\CategoryPlanning;
use AcMarche\Travaux\Entity\InterventionPlanning;
use AcMarche\Travaux\Form\PlanningType;
use AcMarche\Travaux\Planning\DateProvider;
use AcMarche\Travaux\Planning\TreatmentDates;
use AcMarche\Travaux\Repository\CategorieRepository;
use AcMarche\Travaux\Repository\CategoryPlanningRepository;
use AcMarche\Travaux\Repository\EmployeRepository;
use AcMarche\Travaux\Repository\EtatRepository;
use AcMarche\Travaux\Repository\InterventionPlanningRepository;
use AcMarche\Travaux\Repository\PrioriteRepository;
use AcMarche\Travaux\Spreadsheet\SpreadsheetDownloaderTrait;
use AcMarche\Travaux\Spreadsheet\XlsGenerator;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/planning')]
#[IsGranted('ROLE_TRAVAUX_PLANNING')]
class PlanningController extends AbstractController
{
    use SpreadsheetDownloaderTrait;

    const CATEGORY_SELECTED = 'planning_category';

    public function __construct(
        private InterventionPlanningRepository $interventionPlanningRepository,
        private CategoryPlanningRepository $categoryPlanningRepository,
        private EtatRepository $etatRepository,
        private PrioriteRepository $prioriteRepository,
        private CategorieRepository $categorieRepository,
        private EmployeRepository $employeRepository,
        private DateProvider $dateProvider,
        private XlsGenerator $xlsGenerator,
        private AbsenceUtils $absenceUtils
    ) {
    }

    #[Route(path: '/listing/{yearmonth}/{categoryPlanning}', name: 'planning_index')]
    public function index(string $yearmonth = null, ?CategoryPlanning $categoryPlanning = null): Response
    {
        $dateSelected = Carbon::now()->toImmutable();
        if ($yearmonth) {
            $dateSelected = $this->dateProvider->createDateFromYearMonth($yearmonth);
        } else {
            $yearmonth = $dateSelected->format('Y'.'-'.$dateSelected->month);
        }

        $interventions = $this->interventionPlanningRepository->findByMonthAndCategory($yearmonth, $categoryPlanning);
        $days = $this->dateProvider->daysOfMonth($dateSelected);
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

        foreach ($interventions as $intervention) {
            $this->absenceUtils->setVacationToEmployes($intervention->employes->toArray());
        }

        return $this->render('@AcMarcheTravaux/planning/index.html.twig', [
            'interventions' => $interventions,
            'dateSelected' => $dateSelected,
            'next' => $next,
            'today' => $today,
            'weekdays' => $this->dateProvider->weekDaysName(),
            'previous' => $previous,
            'weeks' => $weeks,
            'data' => $data,
            'categorySelected' => $categoryPlanning,
            'categories' => $this->categoryPlanningRepository->findAllOrdered(),
        ]);
    }

    #[Route(path: '/new/{date}/{category}', name: 'planning_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_TRAVAUX_ADD')]
    public function new(Request $request, string $date = null, ?CategoryPlanning $category = null): Response
    {
        if (!$date) {
            $dateSelected = new \DateTime();
        } else {
            $dateSelected = \DateTime::createFromFormat('Y-m-d', $date);
        }

        $request->getSession()->set(self::CATEGORY_SELECTED, $category?->getId());
        $intervention = new InterventionPlanning();
        $intervention->category = $category;
        $intervention->datesCollection = new ArrayCollection();
        $intervention->datesCollection->add($dateSelected);

        $form = $this->createForm(PlanningType::class, $intervention)
            ->add('saveAndAdd', SubmitType::class, [
                'label' => 'Sauvegarder puis ajouter une autre',
                'attr' => ['class' => 'btn-success'],
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $user = $this->getUser();
            $intervention->user_add = $user->getUserIdentifier();

            TreatmentDates::setDatesFromCollection($intervention, $data);

            if (count($intervention->dates) === 0) {
                $this->addFlash('danger', 'Il doit y avoir au moins 1 date d\'encodée');

                return $this->redirectToRoute('planning_new');
            }

            $this->interventionPlanningRepository->persist($intervention);
            $this->interventionPlanningRepository->flush();

            return $form->get('saveAndAdd')->isClicked()
                ? $this->redirectToRoute('planning_new')
                : $this->redirectToRoute('planning_show', array('id' => $intervention->getId()));
        }

        $absents = $this->absenceUtils->findAbsentByDateAndCategory($dateSelected, $category);

        return $this->render(
            '@AcMarcheTravaux/planning/new.html.twig',
            array(
                'form' => $form->createView(),
                'dateSelected' => $dateSelected,
                'absents' => $absents,
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
        $request->getSession()->set(self::CATEGORY_SELECTED, $intervention->category?->getId());
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

    #[Route(path: '/{id}/delete', name: 'planning_delete', methods: ['POST'])]
    public function delete(Request $request, InterventionPlanning $intervention): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$intervention->getId(), $request->request->get('_token'))) {
            $this->interventionPlanningRepository->remove($intervention);
            $this->interventionPlanningRepository->flush();
            $this->addFlash('success', 'L\'intervention a bien été supprimée.');
        }

        return $this->redirectToRoute('planning_index');
    }

    #[Route(path: '/query/autocomplete', name: 'planning_auto_complete', methods: ['GET'])]
    public function autoCompleteRequest(Request $request): JsonResponse
    {
        $query = $request->query->get('query');
        $category = null;
        if ($request->getSession()->has(self::CATEGORY_SELECTED)) {
            //   $category = $request->getSession()->get(self::CATEGORY_SELECTED);
        }
        $employes = $this->employeRepository->searchForAutocomplete($query, $category);
        $results = ['results' => []];
        foreach ($employes as $employe) {
            $results['results'][] = ['value' => $employe->getId(), 'text' => $employe->nom.' '.$employe->prenom];
        }
        $results['next_page'] = null;

        return $this->json($results);
    }
}
