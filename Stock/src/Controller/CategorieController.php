<?php

namespace AcMarche\Stock\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\Persistence\ManagerRegistry;
use AcMarche\Stock\Entity\Categorie;
use AcMarche\Stock\Form\CategorieType;
use AcMarche\Stock\Repository\CategorieRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/categorie')]
#[IsGranted('ROLE_TRAVAUX_STOCK')]
class CategorieController extends AbstractController
{
    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }
    #[Route(path: '/', name: 'stock_categorie_index', methods: ['GET'])]
    public function index(CategorieRepository $categorieRepository) : Response
    {
        return $this->render(
            '@AcMarcheStock/categorie/index.html.twig',
            [
                'categories' => $categorieRepository->getAll(),
            ]
        );
    }
    #[Route(path: '/new', name: 'stock_categorie_new', methods: ['GET', 'POST'])]
    public function new(Request $request) : Response
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->managerRegistry->getManager();
            $entityManager->persist($categorie);
            $entityManager->flush();
            $this->addFlash('success', 'La catégorie a bien été crée.');

            return $this->redirectToRoute('stock_categorie_index');
        }
        return $this->render(
            '@AcMarcheStock/categorie/new.html.twig',
            [
                'categorie' => $categorie,
                'form' => $form->createView(),
            ]
        );
    }
    #[Route(path: '/{id}', name: 'stock_categorie_show', methods: ['GET'])]
    public function show(Categorie $categorie) : Response
    {
        return $this->render(
            '@AcMarcheStock/categorie/show.html.twig',
            [
                'categorie' => $categorie,
            ]
        );
    }
    #[Route(path: '/{id}/edit', name: 'stock_categorie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Categorie $categorie) : Response
    {
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->managerRegistry->getManager()->flush();

            $this->addFlash('success', 'La catégorie a bien été modifiée.');

            return $this->redirectToRoute(
                'stock_categorie_index',
                [
                    'id' => $categorie->getId(),
                ]
            );
        }
        return $this->render(
            '@AcMarcheStock/categorie/edit.html.twig',
            [
                'categorie' => $categorie,
                'form' => $form->createView(),
            ]
        );
    }
    #[Route(path: '/{id}', name: 'stock_categorie_delete', methods: ['POST'])]
    public function delete(Request $request, Categorie $categorie) : RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete' . $categorie->getId(), $request->request->get('_token'))) {
            $entityManager = $this->managerRegistry->getManager();
            $entityManager->remove($categorie);
            $entityManager->flush();
            $this->addFlash('success', 'La catégorie a bien été supprimée.');
        }
        return $this->redirectToRoute('stock_categorie_index');
    }
}
