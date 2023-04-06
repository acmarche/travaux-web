<?php

namespace AcMarche\Travaux\Controller;

use AcMarche\Travaux\Entity\CategoryPlanning;
use AcMarche\Travaux\Entity\Employe;
use AcMarche\Travaux\Export\PdfDownloaderTrait;
use AcMarche\Travaux\Planning\DateProvider;
use AcMarche\Travaux\Planning\PlanningUtils;
use AcMarche\Travaux\Repository\InterventionPlanningRepository;
use AcMarche\Travaux\Repository\InterventionRepository;
use AcMarche\Travaux\Spreadsheet\SpreadsheetDownloaderTrait;
use PhpOffice\PhpSpreadsheet\Cell\CellAddress;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\UnicodeString;

#[Route(path: '/export')]
#[IsGranted('ROLE_TRAVAUX')]
class ExportController extends AbstractController
{
    use PdfDownloaderTrait;
    use SpreadsheetDownloaderTrait;

    public function __construct(
        private InterventionRepository $interventionRepository,
        private InterventionPlanningRepository $interventionPlanningRepository,
        private DateProvider $dateProvider
    ) {
    }

    #[Route(path: '/intervention/pdf/{archive}', name: 'intervention_export_pdf', methods: ['GET'])]
    public function interventionPdf(Request $request, $archive = false): Response
    {
        $session = $request->getSession();
        $args = [];
        if ($archive) {
            if ($session->has("intervention_archive_search")) {
                $args = unserialize($session->get("intervention_archive_search"));
            }
        } elseif ($session->has("intervention_search")) {
            $args = unserialize($session->get("intervention_search"));
        }
        $interventions = $this->interventionRepository->search($args, $archive);

        $html = $this->renderView(
            '@AcMarcheTravaux/pdf/interventions.html.twig',
            [
                'interventions' => $interventions,
                'title' => 'Interventions',
            ]
        );
        $name = sprintf('interventions-%s.pdf', date('Y-m-d'));
        $this->pdf->setOption('footer-right', '[page]/[toPage]');

        return new Response(
            $this->pdf->getOutputFromHtml($html),
            Response::HTTP_OK,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$name.'.pdf"',
            )
        );
    }

    #[Route(path: '/planning/pdf/{yearmonth}/{categoryPlanning}', name: 'planning_export_pdf', methods: ['GET'])]
    public function planningPdf(
        string $yearmonth,
        ?CategoryPlanning $categoryPlanning = null
    ): Response {
        $interventions = $this->interventionPlanningRepository->findByMonthAndCategory($yearmonth, $categoryPlanning);

        $html = $this->renderView(
            '@AcMarcheTravaux/pdf/planning.html.twig',
            [
                'interventions' => $interventions,
                'title' => ' pour le mois de '.$yearmonth,
            ]
        );
        $name = sprintf('planning-%s.pdf', date('Y-m-d'));
        $this->pdf->setOption('footer-right', '[page]/[toPage]');

        return $this->downloadPdf($html, $name);
    }

    #[Route(path: '/planning/pdf/weekly/{year}/{week}/{categoryPlanning}', name: 'planning_export_pdf_weekly', methods: ['GET'])]
    public function planningWeeklyPdf(int $year, int $week, ?CategoryPlanning $categoryPlanning = null): Response
    {
        $interventions = $this->interventionPlanningRepository->findByWeekAndCategory(
            $year,
            $week,
            $categoryPlanning
        );
        $html = $this->renderView(
            '@AcMarcheTravaux/pdf/planning.html.twig',
            [
                'interventions' => $interventions,
                'title' => 'Interventions de la semaine '.$week,
            ]
        );
        $name = sprintf('planning-%s.pdf', date('Y-m-d'));

        return $this->downloadPdf($html, $name, false);
    }

    #[Route(path: '/planning/pdf/daily/{date}/{categoryPlanning}', name: 'planning_export_pdf_daily', methods: ['GET'])]
    public function planningDailyPdf(\DateTime $date, ?CategoryPlanning $categoryPlanning = null): Response
    {
        $interventions = $this->interventionPlanningRepository->findPlanningByDayAndCategory(
            $date,
            $categoryPlanning
        );
        $html = $this->renderView(
            '@AcMarcheTravaux/pdf/planning.html.twig',
            [
                'interventions' => $interventions,
                'title' => 'Interventions du jour '.$date->format('d-m-Y'),
            ]
        );
        $name = sprintf('planning-%s.pdf', date('Y-m-d'));

        return $this->downloadPdf($html, $name, false);
    }

    #[Route(path: '/planning/xls/{yearmonth}/{categoryPlanning}', name: 'planning_export_xls', methods: ['GET'])]
    public function planningMonthXls(
        string $yearmonth,
        ?CategoryPlanning $categoryPlanning = null
    ): Response {
        if (!$categoryPlanning) {
            $this->addFlash('danger', 'Vous dever d\'abord choisir une équipe pour exporter.');

            return $this->redirectToRoute('planning_index');
        }

        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $interventions = $this->interventionPlanningRepository->findByMonthAndCategory($yearmonth, $categoryPlanning);
        $ouvriers = PlanningUtils::extractOuvriers($interventions);

        $this->setTitles($worksheet, $yearmonth, $ouvriers, $categoryPlanning);

        $ligne = 10;
        foreach ($interventions as $intervention) {
            $lettre = 'A';
            $worksheet
                ->setCellValue($lettre++.$ligne, (new UnicodeString($intervention->description))->truncate(120, '...'))
                ->setCellValue($lettre++.$ligne, $intervention->lieu)
                ->setCellValue($lettre++.$ligne, $intervention->horaire);
            foreach ($intervention->getEmployes() as $employe) {
                $worksheet
                    ->setCellValue($lettre++.$ligne, $employe->nom);
            }
            $ligne++;
        }

        $name = 'intervention-'.$yearmonth.'.xlsx';

        return $this->downloadXls($spreadsheet, $name);
    }

    /**
     * @param Worksheet $worksheet
     * @param string $yearmonth
     * @param array|Employe[] $ouvriers
     * @param CategoryPlanning $categoryPlanning
     * @return void
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function setTitles(
        Worksheet $worksheet,
        string $yearmonth,
        array $ouvriers,
        CategoryPlanning $categoryPlanning
    ): void {

        $worksheet->mergeCells('A2:D2');
        $worksheet->mergeCells('A3:D3');
        $worksheet->mergeCells('A4:D4');
        $worksheet->mergeCells('A5:D5');
        $lettre = 'E';

        foreach ($ouvriers as $ouvrier) {
            $ligne = 2;
            $worksheet->mergeCells($lettre.$ligne.':'.$lettre.$ligne + 7);
            $worksheet->setCellValue($lettre.'9', $ouvrier->nom.' '.$ouvrier->prenom);
            //   $worksheet->getStyle($lettre.'9')->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
            $lettre++;
        }

        $lettre = 'A';
        $ligne = 2;
        $worksheet
            ->setCellValue($lettre.$ligne++, 'Commune: '.'Marche-en-Famenne')
            ->setCellValue($lettre.$ligne++, 'Equipe: '.$categoryPlanning->name)
            ->setCellValue($lettre.$ligne++, 'Date: '.$yearmonth)
            ->setCellValue($lettre.$ligne++, 'Signature/Cachet: ');

        $ligne = 6;

        $worksheet
            ->setCellValue($lettre++.$ligne, 'Description tâche')
            ->setCellValue($lettre++.$ligne, 'Localisation')
            ->setCellValue($lettre++.$ligne, 'Plage horaire')
            ->setCellValue($lettre++.$ligne, 'Présences');


    }

}
