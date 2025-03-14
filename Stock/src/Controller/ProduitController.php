<?php

namespace AcMarche\Stock\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\Persistence\ManagerRegistry;
use AcMarche\Stock\Entity\Produit;
use AcMarche\Stock\Form\ProduitType;
use AcMarche\Stock\Form\SearchProduitType;
use AcMarche\Stock\Repository\ProduitRepository;
use AcMarche\Stock\Service\Logger;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/produit')]
#[IsGranted('ROLE_TRAVAUX_STOCK')]
class ProduitController extends AbstractController
{
    public function __construct(private Logger $logger, private ManagerRegistry $managerRegistry)
    {
    }
    #[Route(path: '/', name: 'stock_produit_index', methods: ['GET'])]
    public function index(Request $request, ProduitRepository $produitRepository) : Response
    {
        $session = $request->getSession();
        $key = 'produit_search';
        $data = array();
        if ($session->has($key)) {
            $data = unserialize($session->get($key));
        }
        $search_form = $this->createForm(
            SearchProduitType::class,
            $data,
            array(
                'method' => 'GET',
            )
        );
        $search_form->handleRequest($request);
        if ($search_form->isSubmitted() && $search_form->isValid()) {
            $data = $search_form->getData();

            if ($search_form->get('raz')->isClicked()) {
                $session->remove($key);
                $this->addFlash('info', 'La recherche a bien été réinitialisée.');

                return $this->redirectToRoute('stock_produit_index');
            }
        }
        $session->set($key, serialize($data));
        $produits = $produitRepository->search($data);
        return $this->render(
            '@AcMarcheStock/produit/index.html.twig',
            array(
                'search_form' => $search_form->createView(),
                'produits' => $produits,
            )
        );
    }
    #[Route(path: '/new', name: 'stock_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request) : Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->managerRegistry->getManager();
            $entityManager->persist($produit);
            $entityManager->flush();
            $this->addFlash('success', 'Le produit a bien été créé.');

            return $this->redirectToRoute('stock_produit_index');
        }
        return $this->render(
            '@AcMarcheStock/produit/new.html.twig',
            [
                'produit' => $produit,
                'form' => $form,
            ]
        );
    }
    #[Route(path: '/{id}', name: 'stock_produit_show', methods: ['GET'])]
    public function show(Produit $produit) : Response
    {
        return $this->render(
            '@AcMarcheStock/produit/show.html.twig',
            [
                'produit' => $produit,
            ]
        );
    }
    #[Route(path: '/{id}/edit', name: 'stock_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit) : Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->logger->log($produit, $form->getData()->getQuantite());

            $this->managerRegistry->getManager()->flush();

            $this->addFlash('success', 'Le produit a bien été modifié.');

            return $this->redirectToRoute(
                'stock_produit_index',
                [
                    'id' => $produit->getId(),
                ]
            );
        }
        return $this->render(
            '@AcMarcheStock/produit/edit.html.twig',
            [
                'produit' => $produit,
                'form' => $form,
            ]
        );
    }
    #[Route(path: '/{id}', name: 'stock_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit) : RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete' . $produit->getId(), $request->request->get('_token'))) {
            $entityManager = $this->managerRegistry->getManager();
            $entityManager->remove($produit);
            $entityManager->flush();
            $this->addFlash('success', 'Le produit a bien été supprimé.');
        }
        return $this->redirectToRoute('stock_produit_index');
    }
}
