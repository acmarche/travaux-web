<?php

namespace AcMarche\Travaux\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use AcMarche\Travaux\Entity\Batiment;
use AcMarche\Travaux\Form\BatimentType;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Batiment controller.
 */
#[Route(path: '/batiment')]
#[IsGranted('ROLE_TRAVAUX_ADMIN')]
class BatimentController extends AbstractController
{
    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }
    /**
     * Lists all Batiment entities.
     */
    #[Route(path: '/', name: 'batiment', methods: ['GET'])]
    public function index() : Response
    {
        $em = $this->managerRegistry->getManager();
        $entities = $em->getRepository(Batiment::class)->findAll();
        return $this->render(
            '@AcMarcheTravaux/batiment/index.html.twig',
            array(
                'entities' => $entities,
            )
        );
    }
    /**
     * Displays a form to create a new Batiment entity.
     *
     *
     */
    #[Route(path: '/new', name: 'batiment_new', methods: ['GET', 'POST'])]
    public function new(Request $request) : Response
    {
        $batiment = new Batiment();
        $form = $this->createForm(BatimentType::class, $batiment)
            ->add('Create', SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->managerRegistry->getManager();

            $em->persist($batiment);
            $em->flush();

            $this->addFlash('success', 'Le bâtiment a bien été créé.');

            return $this->redirectToRoute('batiment_show', array('id' => $batiment->getId()));
        }
        return $this->render(
            '@AcMarcheTravaux/batiment/new.html.twig',
            array(
                'entity' => $batiment,
                'form' => $form->createView(),
            )
        );
    }
    /**
     * Finds and displays a Batiment entity.
     *
     *
     */
    #[Route(path: '/{id}', name: 'batiment_show', methods: ['GET'])]
    public function show(Batiment $batiment) : Response
    {
        return $this->render(
            '@AcMarcheTravaux/batiment/show.html.twig',
            array(
                'entity' => $batiment,
            )
        );
    }
    /**
     * Displays a form to edit an existing Batiment entity.
     *
     *
     */
    #[Route(path: '/{id}/edit', name: 'batiment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Batiment $batiment) : Response
    {
        $em = $this->managerRegistry->getManager();
        $editForm = $this->createForm(BatimentType::class, $batiment)
            ->add('Update', SubmitType::class);
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Le bâtiment a bien été modifié.');

            return $this->redirectToRoute('batiment_show', array('id' => $batiment->getId()));
        }
        return $this->render(
            '@AcMarcheTravaux/batiment/edit.html.twig',
            array(
                'entity' => $batiment,
                'edit_form' => $editForm->createView(),
            )
        );
    }
    /**
     * Deletes a Batiment entity.
     */
    #[Route(path: '/{id}', name: 'batiment_delete', methods: ['POST'])]
    public function delete(Request $request, Batiment $batiment) : RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$batiment->getId(), $request->request->get('_token'))) {

            $em = $this->managerRegistry->getManager();

            $em->remove($batiment);
            $em->flush();

            $this->addFlash('success', 'Le bâtiment a bien été supprimé.');
        }
        return $this->redirectToRoute('batiment');
    }
}
