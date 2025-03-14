<?php

namespace AcMarche\Travaux\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use AcMarche\Travaux\Entity\Categorie;
use AcMarche\Travaux\Form\CategorieType;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;


#[Route(path: '/categorie')]
#[IsGranted('ROLE_TRAVAUX_ADMIN')]
class CategorieController extends AbstractController
{
    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }
    /**
     * Lists all Categorie entities.
     *
     *
     */
    #[Route(path: '/', name: 'categorie', methods: ['GET'])]
    public function index() : Response
    {
        $em = $this->managerRegistry->getManager();
        $entities = $em->getRepository(Categorie::class)->findAll();
        return $this->render(
            '@AcMarcheTravaux/categorie/index.html.twig',
            array(
                'entities' => $entities,
            )
        );
    }
    /**
     * Displays a form to create a new Categorie entity.
     *
     *
     */
    #[Route(path: '/new', name: 'categorie_new', methods: ['GET', 'POST'])]
    public function new(Request $request) : Response
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie)
            ->add('Create', SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->managerRegistry->getManager();

            $em->persist($categorie);
            $em->flush();

            $this->addFlash('success', 'La catégorie a bien été crée.');

            return $this->redirectToRoute('categorie_show', array('id' => $categorie->getId()));
        }
        return $this->render(
            '@AcMarcheTravaux/categorie/new.html.twig',
            array(
                'entity' => $categorie,
                'form' => $form,
            )
        );
    }
    /**
     * Finds and displays a Categorie entity.
     *
     *
     */
    #[Route(path: '/{id}', name: 'categorie_show', methods: ['GET'])]
    public function show(Categorie $categorie) : Response
    {
        return $this->render(
            '@AcMarcheTravaux/categorie/show.html.twig',
            array(
                'entity' => $categorie,
            )
        );
    }
    /**
     * Displays a form to edit an existing Categorie entity.
     *
     *
     */
    #[Route(path: '/{id}/edit', name: 'categorie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Categorie $categorie) : Response
    {
        $editForm = $this->createForm(CategorieType::class, $categorie)
            ->add('Update', SubmitType::class);
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->managerRegistry->getManager();
            $em->flush();
            $this->addFlash('success', 'La catégorie a bien été mise à jour.');

            return $this->redirectToRoute('categorie_show', array('id' => $categorie->getId()));
        }
        return $this->render(
            '@AcMarcheTravaux/categorie/edit.html.twig',
            array(
                'entity' => $categorie,
                'edit_form' => $editForm->createView(),
            )
        );
    }
    /**
     * Deletes a Categorie entity.
     */
    #[Route(path: '/{id}', name: 'categorie_delete', methods: ['POST'])]
    public function delete(Request $request, Categorie $categorie) : RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$categorie->getId(), $request->request->get('_token'))) {

            $em = $this->managerRegistry->getManager();

            $intervention = $categorie->getIntervention();

            if (count($intervention) > 0) {
                $this->addFlash(
                    'warning',
                    "Cette catégorie ne peut être supprimée car des intervention sont classés dans celle-ci"
                );

                return $this->redirectToRoute('categorie');
            }

            $em->remove($categorie);
            $em->flush();

            $this->addFlash('success', 'La catégorie a bien été supprimée.');
        }
        return $this->redirectToRoute('categorie');
    }
}
