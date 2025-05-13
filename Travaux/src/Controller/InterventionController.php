<?php

namespace AcMarche\Travaux\Controller;

use AcMarche\Travaux\Entity\Intervention;
use AcMarche\Travaux\Event\InterventionEvent;
use AcMarche\Travaux\Form\InterventionType;
use AcMarche\Travaux\Form\Search\SearchInterventionType;
use AcMarche\Travaux\Repository\CategorieRepository;
use AcMarche\Travaux\Repository\InterventionRepository;
use AcMarche\Travaux\Repository\SuiviRepository;
use AcMarche\Travaux\Service\FileHelper;
use AcMarche\Travaux\Service\InterventionWorkflow;
use AcMarche\Travaux\Service\TravauxUtils;
use AcMarche\Travaux\Service\WorkflowEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/intervention')]
#[IsGranted('ROLE_TRAVAUX')]
class InterventionController extends AbstractController
{
    public function __construct(
        private TravauxUtils $travauxUtils,
        private FileHelper $fileHelper,
        private InterventionWorkflow $workflow,
        private EventDispatcherInterface $eventDispatcher,
        private InterventionRepository $interventionRepository,
        private CategorieRepository $categorieRepository,
        private SuiviRepository $suiviRepository,
    ) {
    }

    #[Route(path: '/', name: 'intervention')]
    #[Route(path: '/ancre/{anchor}/', name: 'intervention_anchor', methods: ['GET'])]
    public function index(Request $request, $anchor = null): Response
    {
        $key = "intervention_search";
        $data = [];
        $session = $request->getSession();
        if ($session->has($key)) {
            $data = unserialize($session->get($key));
        }
        if ($categorieIntervention = $this->travauxUtils->getCategorieDefault('Intervention')) {
            $data['categorie'] = $categorieIntervention->getId();
        }

        $user = $this->getUser();
        $data['current_user'] = $user;
        $data['archive'] = 0;
        $data['categorie'] = 3;
        $data['place'] = WorkflowEnum::PUBLISHED;
        $data = array_merge($data, $this->travauxUtils->getConstraintsForUser());
        $search_form = $this->createForm(
            SearchInterventionType::class,
            $data,
            [
                'method' => 'GET',
            ],
        );
        $search_form->handleRequest($request);
        if ($search_form->isSubmitted() && $search_form->isValid()) {
            $data = $search_form->getData();

            if ($search_form->get('raz')->isClicked()) {
                $session->remove($key);
                $this->addFlash('info', 'La recherche a bien été réinitialisée.');

                return $this->redirectToRoute('intervention');
            }
        }
        $session->set($key, serialize($data));
        $interventions = $this->interventionRepository->search($data, true);
        $this->travauxUtils->setLastSuivisForInterventions($interventions);

        return $this->render(
            '@AcMarcheTravaux/intervention/index.html.twig',
            [
                'search_form' => $search_form->createView(),
                'interventions' => $interventions,
                'anchor' => $anchor,
            ],
        );
    }

    #[Route(path: '/new', name: 'intervention_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_TRAVAUX_ADD')]
    public function new(Request $request): Response
    {
        $intervention = new Intervention();
        $form = $this->createForm(InterventionType::class, $intervention);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $intervention->setUserAdd($user->getUserIdentifier());

            $category = $this->categorieRepository->find(3);
            $intervention->categorie = $category;

            $this->interventionRepository->persist($intervention);
            $this->interventionRepository > flush();

            $this->workflow->newIntervention($intervention);

            $this->interventionRepository->flush();
            $this->addFlash('success', 'L\'intervention a bien été crée.');

            $event = new InterventionEvent($intervention, null);
            $this->eventDispatcher->dispatch($event, InterventionEvent::INTERVENTION_NEW);

            return $this->redirectToRoute('intervention_show', ['id' => $intervention->getId()]);
        }

        $response = new Response(null, $form->isSubmitted() ? Response::HTTP_ACCEPTED : Response::HTTP_OK);

        return $this->render(
            '@AcMarcheTravaux/intervention/new.html.twig',
            [
                'entity' => $intervention,
                'form' => $form,
            ],
            $response,
        );
    }

    #[Route(path: '/{id}/show', name: 'intervention_show', methods: ['GET'])]
    public function show(Intervention $intervention): Response
    {
        $deleteFormSuivis = $this->createSuivisDeleteForm($intervention->getId());
        $suivis = $this->suiviRepository->search(
            ['intervention' => $intervention],
        );

        return $this->render(
            '@AcMarcheTravaux/intervention/show.html.twig',
            [
                'intervention' => $intervention,
                'suivis' => $suivis,
                'delete_form_suivis' => $deleteFormSuivis->createView(),
                'pdf' => false,
            ],
        );
    }

    #[Route(path: '/{id}/edit', name: 'intervention_edit', methods: ['GET', 'POST'])]
    #[IsGranted('edit', subject: 'intervention')]
    public function edit(Request $request, Intervention $intervention): Response
    {
        $form = $this->createForm(InterventionType::class, $intervention);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->interventionRepository->flush();

            $this->addFlash('success', 'L\'intervention a bien été modifiée.');

            return $this->redirectToRoute('intervention_show', ['id' => $intervention->getId()]);
        }

        $response = new Response(null, $form->isSubmitted() ? Response::HTTP_ACCEPTED : Response::HTTP_OK);

        return $this->render(
            '@AcMarcheTravaux/intervention/edit.html.twig',
            [
                'entity' => $intervention,
                'form' => $form,
            ],
            $response,
        );
    }

    #[Route(path: '/{id}', name: 'intervention_delete', methods: ['POST'])]
    #[IsGranted('delete', subject: 'intervention')]
    public function delete(Request $request, Intervention $intervention): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$intervention->getId(), $request->request->get('_token'))) {
            try {
                $this->fileHelper->deleteAllDocs($intervention);
                $this->interventionRepository->remove($intervention);

                $this->interventionRepository->flush();
                $this->addFlash('success', 'L\'intervention a bien été supprimée.');
            } catch (IOException $exception) {
                $this->addFlash("danger", "Erreur de la suppression des pièce jointes: ".$exception->getMessage());
            }
        }

        return $this->redirectToRoute('intervention');
    }

    #[Route(path: '/suivis/delete/{id}', name: 'suivis_delete', methods: ['POST'])]
    public function deleteSuivis(Request $request, Intervention $intervention): RedirectResponse
    {
        $form = $this->createSuivisDeleteForm($intervention->getId());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $suivis = $request->get('suivis', []);

            if ((is_countable($suivis) ? count($suivis) : 0) < 1) {
                $this->addFlash('warning', "Aucun suivis sélectionné");

                return $this->redirectToRoute('intervention_show', ['id' => $intervention->getid()]);
            }

            $user = $this->getUser();

            foreach ($suivis as $suivis_id) {
                $suivi = $this->suiviRepository->find($suivis_id);

                if ($suivi) {
                    $userAdd = $suivi->getUserAdd();
                    if ($userAdd == $user->getUserIdentifier()) {
                        $this->suiviRepository->remove($suivi);
                    } else {
                        $this->addFlash('danger', "Seul celui qui a ajouté le suivi peut le supprimer");
                    }
                }
            }

            $this->interventionRepository->flush();
            $this->addFlash('success', 'Le(s) suivi(s) ont bien été supprimé(s)');

            return $this->redirectToRoute('intervention_show', ['id' => $intervention->getId()]);
        }

        return $this->redirectToRoute('intervention');
    }

    /**
     * Creates a form to delete a Suivis entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return FormInterface The form
     */
    private function createSuivisDeleteForm($intervention_id): FormInterface
    {
        return $this
            ->createFormBuilder()
            ->setAction($this->generateUrl('suivis_delete', ['id' => $intervention_id]))
            ->setMethod(Request::METHOD_DELETE)
            ->getForm();
    }

    #[Route(path: '/affecte/prive', name: 'intervention_prive', methods: ['GET'])]
    public function indexPrive(Request $request): Response
    {
        $interventions = $this->interventionRepository->findPrive(false);

        return $this->render(
            '@AcMarcheTravaux/intervention/prive.html.twig',
            [
                'interventions' => $interventions,
            ],
        );
    }
}
