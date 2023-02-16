<?php

namespace AcMarche\Travaux\Controller;

use Doctrine\Persistence\ManagerRegistry;
use AcMarche\Travaux\Entity\Intervention;
use Knp\Snappy\Pdf;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Export controller.
 */
#[Route(path: '/export')]
#[IsGranted('ROLE_TRAVAUX')]
class ExportController extends AbstractController
{
    public function __construct(private Pdf $pdf, private ManagerRegistry $managerRegistry)
    {
    }
    #[Route(path: '/pdf/{archive}', name: 'export_pdf', methods: ['GET'])]
    public function pdf(Request $request, $archive = false) : Response
    {
        $em = $this->managerRegistry->getManager();
        $session = $request->getSession();
        $args = [];
        if ($archive) {
            if ($session->has("intervention_archive_search")) {
                $args = unserialize($session->get("intervention_archive_search"));
            }
        } elseif ($session->has("intervention_search")) {
            $args = unserialize($session->get("intervention_search"));
        }
        $interventions = $em->getRepository(Intervention::class)->search(
            $args
        );
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
}
