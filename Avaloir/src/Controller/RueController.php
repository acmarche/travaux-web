<?php

namespace AcMarche\Avaloir\Controller;

use AcMarche\Avaloir\Entity\Rue;
use AcMarche\Avaloir\Form\RueType;
use AcMarche\Avaloir\Repository\AvaloirRepository;
use AcMarche\Avaloir\Repository\RueRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/rue')]
#[IsGranted('ROLE_TRAVAUX_AVALOIR')]
class RueController extends AbstractController
{
    public function __construct(
        private RueRepository $rueRepository,
        private AvaloirRepository $avaloirRepository,
        private ManagerRegistry $managerRegistry
    ) {
    }

    #[Route(path: '/', name: 'rue', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $rues = $this->rueRepository->findAll();

        return $this->render(
            '@AcMarcheAvaloir/rue/index.html.twig',
            array(
                'rues' => $rues,
            )
        );
    }

    #[Route(path: '/new', name: 'rue_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $rue = new Rue();
        $form = $this->createForm(RueType::class, $rue)
            ->add('Create', SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->managerRegistry->getManager();
            $em->persist($rue);
            $em->flush();

            $this->addFlash("success", "La rue a bien été ajoutée");

            return $this->redirectToRoute('rue_show', array('id' => $rue->getId()));
        }

        return $this->render(
            '@AcMarcheAvaloir/rue/new.html.twig',
            array(
                'entity' => $rue,
                'form' => $form->createView(),
            )
        );
    }

    #[Route(path: '/{id}', name: 'rue_show', methods: ['GET'])]
    public function show(Rue $rue): Response
    {
        $avaloirs = $this->avaloirRepository->getByRue($rue);

        return $this->render(
            '@AcMarcheAvaloir/rue/show.html.twig',
            array(
                'rue' => $rue,
                'avaloirs' => $avaloirs,
            )
        );
    }

    #[Route(path: '/{id}/edit', name: 'rue_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Rue $rue): Response
    {
        $em = $this->managerRegistry->getManager();
        $editForm = $this->createForm(RueType::class, $rue)
            ->add('Update', SubmitType::class);
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em->flush();
            $this->addFlash("success", "La rue a bien été modifiée");

            return $this->redirectToRoute('rue');
        }

        return $this->render(
            '@AcMarcheAvaloir/rue/edit.html.twig',
            array(
                'entity' => $rue,
                'edit_form' => $editForm->createView(),
            )
        );
    }

    /**
     * Deletes a Rue entity.
     */
    #[Route(path: '/{id}', name: 'rue_delete', methods: ['POST'])]
    public function delete(Request $request, Rue $rue): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$rue->getId(), $request->request->get('_token'))) {
            $em = $this->managerRegistry->getManager();

            $em->remove($rue);
            $em->flush();
            $this->addFlash("success", "La rue a bien été supprimée");
        }

        return $this->redirectToRoute('rue');
    }

    #[Route(path: '/ajax/rueautocomplete/{q}', name: 'rue_autocomplete', methods: ['POST','GET'])]
    public function rueAutocomplete(Request $request, ?string $q = null): JsonResponse
    {
        $query = $request->query->get('q');
        if (!$query) {
            return new JsonResponse([]);
        }
        $rues = $this->rueRepository->search(array('nom' => $query));
        $data = [];
        $i = 0;
        foreach ($rues as $rue) {
            $data[$i]['name'] = $rue->getNom().' '.$rue->getVillage();
            $data[$i]['id'] = $rue->getId();
            ++$i;
        }

        return $this->json($data);
    }

    /**
     * Pour remplir l'auto completion.
     */
    #[Route(path: '/ajax/ruesuggestion', name: 'rue_suggestion', methods: ['GET'])]
    public function rueSuggestion(Request $request): Response
    {
        $query = $request->query->get('q');
        if (! $query) {
            return new JsonResponse([]);
        }
        $expediteurs = $this->rueRepository->search([
            'nom' => $query,
        ]);

        return $this->render('@AcMarcheAvaloir/rue/_list.html.twig', [
            'expediteurs' => $expediteurs,
        ]);
    }
}
