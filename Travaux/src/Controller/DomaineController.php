<?php

namespace AcMarche\Travaux\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use AcMarche\Travaux\Entity\Domaine;
use AcMarche\Travaux\Form\DomaineType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Domaine controller.
 */
#[Route(path: '/domaine')]
#[IsGranted('ROLE_TRAVAUX_ADMIN')]
class DomaineController extends AbstractController
{
    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }
    /**
     * Lists all Domaine entities.
     *
     *
     */
    #[Route(path: '/', name: 'domaine', methods: ['GET'])]
    public function index() : Response
    {
        $em = $this->managerRegistry->getManager();
        $entities = $em->getRepository(Domaine::class)->findAll();
        return $this->render(
            '@AcMarcheTravaux/domaine/index.html.twig',
            array(
                'entities' => $entities,
            )
        );
    }
    /**
     * Displays a form to create a new Domaine entity.
     *
     *
     */
    #[Route(path: '/new', name: 'domaine_new', methods: ['GET', 'POST'])]
    public function new(Request $request) : Response
    {
        $domaine = new Domaine();
        $form = $this->createForm(DomaineType::class, $domaine)
            ->add('Create', SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->managerRegistry->getManager();

            $em->persist($domaine);
            $em->flush();

            $this->addFlash('success', 'Le type a bien été créé.');

            return $this->redirectToRoute('domaine_show', array('id' => $domaine->getId()));
        }
        return $this->render(
            '@AcMarcheTravaux/domaine/new.html.twig',
            array(
                'entity' => $domaine,
                'form' => $form->createView(),
            )
        );
    }
    /**
     * Finds and displays a Domaine entity.
     *
     *
     */
    #[Route(path: '/{id}', name: 'domaine_show', methods: ['GET'])]
    public function show(Domaine $domaine) : Response
    {
        return $this->render(
            '@AcMarcheTravaux/domaine/show.html.twig',
            array(
                'entity' => $domaine,
            )
        );
    }
    /**
     * Displays a form to edit an existing Domaine entity.
     *
     *
     */
    #[Route(path: '/{id}/edit', name: 'domaine_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Domaine $domaine) : Response
    {
        $editForm = $this->createForm(DomaineType::class, $domaine)
            ->add('Update', SubmitType::class);
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->managerRegistry->getManager();
            $em->flush();

            $this->addFlash('success', 'Le type a bien été mis à jour.');

            return $this->redirectToRoute('domaine_show', array('id' => $domaine->getId()));
        }
        return $this->render(
            '@AcMarcheTravaux/domaine/edit.html.twig',
            array(
                'entity' => $domaine,
                'edit_form' => $editForm->createView(),
            )
        );
    }
    /**
     * Deletes a Domaine entity.
     */
    #[Route(path: '/{id}', name: 'domaine_delete', methods: ['POST'])]
    public function delete(Request $request, Domaine $domaine) : RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$domaine->getId(), $request->request->get('_token'))) {

            $em = $this->managerRegistry->getManager();
            $em->remove($domaine);
            $em->flush();

            $this->addFlash('success', 'Le type a bien été supprimé.');
        }
        return $this->redirectToRoute('domaine');
    }
}
