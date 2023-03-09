<?php

namespace AcMarche\Travaux\Controller;

use AcMarche\Travaux\Repository\InterventionRepository;
use AcMarche\Travaux\Spreadsheet\SpreadsheetDownloaderTrait;
use AcMarche\Travaux\Spreadsheet\XlsGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class DefaultController extends AbstractController
{
    use SpreadsheetDownloaderTrait;

    public function __construct(
        private InterventionRepository $interventionRepository,
        private XlsGenerator $xlsGenerator
    ) {
    }

    #[Route(path: '/', name: 'homepage')]
    public function index(): RedirectResponse
    {
        if ($this->isGranted("ROLE_TRAVAUX")) {
            return $this->redirectToRoute('intervention', array(), '301');
        }

        return $this->redirectToRoute('app_login');
    }

    #[Route(path: '/grh/{year}/{xls}', name: 'grh_export')]
    public function grh(int $year = 2022, bool $xls = false): Response
    {
        $debut = \DateTime::createFromFormat('Y-m-d', $year.'-01-01');
        $end = \DateTime::createFromFormat('Y-m-d', $year.'-12-31');
        $interventions = $this->interventionRepository->findByDates($debut, $end);

        if ($xls) {
            $spreadSheet = $this->xlsGenerator->forGrh($interventions);

            return $this->downloadXls($spreadSheet, 'apptravaux-'.$year.'.xls');
        }

        return $this->render('@AcMarcheTravaux/intervention/grh.html.twig', [
            'interventions' => $interventions,
            'year' => $year,
        ]);
    }

    #[Route(path: '/documentation', name: 'documentation')]
    #[IsGranted('ROLE_TRAVAUX')]
    public function documentation(): Response
    {
        return $this->render('@AcMarcheTravaux/default/documentation.html.twig');
    }
}
