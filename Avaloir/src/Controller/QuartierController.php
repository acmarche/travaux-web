<?php

namespace AcMarche\Avaloir\Controller;

use AcMarche\Avaloir\Entity\Quartier;
use AcMarche\Avaloir\Form\QuartierType;
use AcMarche\Avaloir\Repository\AvaloirRepository;
use AcMarche\Avaloir\Repository\RueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/quartier')]
#[IsGranted('ROLE_TRAVAUX_AVALOIR')]
class QuartierController extends AbstractController
{
    public function __construct(
        private RueRepository $rueRepository,
        private AvaloirRepository $avaloirRepository,
        private ManagerRegistry $managerRegistry
    ) {
    }

    #[Route(path: '/', name: 'quartier', methods: ['GET'])]
    public function index(): Response
    {
        $em = $this->managerRegistry->getManager();
        $entities = $em->getRepository(Quartier::class)->search(array());

        return $this->render(
            '@AcMarcheAvaloir/quartier/index.html.twig',
            array(
                'entities' => $entities,
            )
        );
    }

    /**
     * Displays a form to create a new Quartier entity.
     *
     *
     */
    #[Route(path: '/new', name: 'quartier_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $quartier = new Quartier();
        $form = $this->createForm(QuartierType::class, $quartier)
            ->add('Create', SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->managerRegistry->getManager();
            $em->persist($quartier);
            $em->flush();

            $this->addFlash("success", "Le quartier a bien été créé");

            return $this->redirectToRoute('quartier');
        }

        return $this->render(
            '@AcMarcheAvaloir/quartier/new.html.twig',
            array(
                'entity' => $quartier,
                'form' => $form->createView(),
            )
        );
    }

    #[Route(path: '/{id}', name: 'quartier_show', methods: ['GET'])]
    public function show(Quartier $quartier): Response
    {
        $rues = $this->rueRepository->getByQuartier($quartier);
        foreach ($rues as $rue) {
            $avaloirs = $this->avaloirRepository->getByRue($rue);
            $rue->setAvaloirs(new ArrayCollection($avaloirs));
        }
        $data = [];
        foreach ($rues as $rue) {
            $data[$rue->getVillage()][] = $rue;
        }

        return $this->render(
            '@AcMarcheAvaloir/quartier/show.html.twig',
            array(
                'data' => $data,
                'quartier' => $quartier,
            )
        );
    }

    /**
     * Displays a form to edit an existing Quartier entity.
     *
     *
     */
    #[Route(path: '/{id}/edit', name: 'quartier_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Quartier $quartier): Response
    {
        $em = $this->managerRegistry->getManager();
        $editForm = $this->createForm(QuartierType::class, $quartier)
            ->add('Update', SubmitType::class);
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em->flush();
            $this->addFlash("success", "Le quartier a bien été modifié");

            return $this->redirectToRoute('quartier');
        }

        return $this->render(
            '@AcMarcheAvaloir/quartier/edit.html.twig',
            array(
                'entity' => $quartier,
                'edit_form' => $editForm->createView(),
            )
        );
    }

    /**
     * Deletes a Quartier entity.
     */
    #[Route(path: '/{id}', name: 'quartier_delete', methods: ['POST'])]
    public function delete(Request $request, Quartier $quartier): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$quartier->getId(), $request->request->get('_token'))) {

            $em = $this->managerRegistry->getManager();

            $em->remove($quartier);
            $em->flush();

            $this->addFlash("success", "Le quartier a bien été effacé");
        }

        return $this->redirectToRoute('quartier');
    }
}
