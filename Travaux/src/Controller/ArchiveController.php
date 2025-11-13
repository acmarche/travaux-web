<?php

namespace AcMarche\Travaux\Controller;

use AcMarche\Travaux\Entity\Intervention;
use AcMarche\Travaux\Event\InterventionEvent;
use AcMarche\Travaux\Form\Search\SearchInterventionType;
use AcMarche\Travaux\Repository\InterventionRepository;
use AcMarche\Travaux\Service\TravauxUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/archive')]
#[IsGranted('ROLE_TRAVAUX')]
class ArchiveController extends AbstractController
{
    public function __construct(
        private readonly InterventionRepository $interventionRepository,
        private readonly TravauxUtils $travauxUtils
    ) {
    }

    #[Route(path: '/', name: 'intervention_archive', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $session = $request->getSession();
        $data = array();
        $key = "intervention_archive_search";
        if ($session->has($key)) {
            $data = unserialize($session->get($key));
        }
        $user = $this->getUser();
        $data['current_user'] = $user;
        $data['archive'] = 1;
        //force archive
        $data['sort'] = 'createdAt';
        /**
         * Doit voir les demandes a valider
         */
        $data['role'] = $this->travauxUtils->setRoleConstraint();

        $search_form = $this->createForm(
            SearchInterventionType::class,
            $data,
            array(
                'action' => $this->generateUrl('intervention_archive'),
                'method' => 'GET',
            )
        );
        $entities = [];
        $search_form->handleRequest($request);
        if ($search_form->isSubmitted() && $search_form->isValid()) {
            $data = $search_form->getData();

            if ($search_form->get('raz')->isClicked()) {
                $session->remove($key);
                $this->addFlash('info', 'La recherche a bien été réinitialisée.');

                return $this->redirectToRoute('intervention_archive');
            }
            $session->set($key, serialize($data));
            $entities = $this->interventionRepository->search($data);
        }

        return $this->render(
            '@AcMarcheTravaux/archive/index.html.twig',
            array(
                'search_form' => $search_form->createView(),
                'entities' => $entities,
                'search' => $search_form->isSubmitted(),
            )
        );
    }

    #[Route(path: '/archiveset/{id}', name: 'intervention_archive_set', methods: ['POST'])]
    public function archiveSet(
        Request $request,
        Intervention $intervention,
        EventDispatcherInterface $dispatcher
    ): RedirectResponse {
        if (!$this->getUser()->hasRole('ROLE_TRAVAUX_REDACTEUR') && !$this->getUser()->hasRole("ROLE_TRAVAUX_ADMIN")) {
            throw $this->createAccessDeniedException('Vous n\'avez pas le droit d\'archiver');
        }
        if ($this->isCsrfTokenValid('archive'.$intervention->getId(), $request->request->get('_token'))) {

            $event = new InterventionEvent($intervention, null);

            $label = $intervention->getArchive() ? 'désarchivée' : 'archivée';

            if ($intervention->getArchive()) {
                $intervention->setArchive(false);
            } else {
                $intervention->setArchive(true);
                $intervention->setCurrentPlace(WorkflowEnum::PUBLISHED->value);//force
                $dispatcher->dispatch($event, InterventionEvent::INTERVENTION_ARCHIVE);
            }

            $this->interventionRepository->persist($intervention);
            $this->interventionRepository->flush();

            $this->addFlash('success', "L'intervention a bien été $label");
        }

        return $this->redirectToRoute('intervention');
    }
}
