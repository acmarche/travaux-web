<?php

namespace AcMarche\Travaux\Controller;

use AcMarche\Travaux\Export\PdfDownloaderTrait;
use AcMarche\Travaux\Repository\InterventionPlanningRepository;
use AcMarche\Travaux\Repository\InterventionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/export')]
#[IsGranted('ROLE_TRAVAUX')]
class ExportController extends AbstractController
{
    use PdfDownloaderTrait;

    public function __construct(
        private InterventionRepository $interventionRepository,
        private InterventionPlanningRepository $interventionPlanningRepository
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

    #[Route(path: '/planning/pdf', name: 'planning_export_pdf', methods: ['GET'])]
    public function planningPdf(Request $request): Response
    {
        $session = $request->getSession();
        $args = [];
        $args = unserialize($session->get("intervention_search"));

        $interventions = $this->interventionPlanningRepository->findAll();

        $html = $this->renderView(
            '@AcMarcheTravaux/pdf/planning.html.twig',
            [
                'interventions' => $interventions,
                'title' => 'Interventions',
            ]
        );
        $name = sprintf('planning-%s.pdf', date('Y-m-d'));
        $this->pdf->setOption('footer-right', '[page]/[toPage]');

        return $this->downloadPdf($html, $name);
    }
}
