<?php

namespace AcMarche\Stock\Controller;

use AcMarche\Stock\Entity\Produit;
use AcMarche\Stock\Repository\CategorieRepository;
use AcMarche\Stock\Repository\ProduitRepository;
use AcMarche\Stock\Service\Logger;
use AcMarche\Stock\Service\SerializeApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController
 * @package AcMarche\Api\Controller
 */
#[Route(path: '/api')]
class ApiController extends AbstractController
{
    public function __construct(
        private ProduitRepository $produitRepository,
        private CategorieRepository $categorieRepository,
        private SerializeApi $serializeApi,
        private Logger $logger
    ) {
    }

    #[Route(path: '/all')]
    public function index(): JsonResponse
    {
        $produits = $this->serializeApi->serializeProduits($this->produitRepository->getAll());
        $categories = $this->serializeApi->serializeCategorie($this->categorieRepository->findAll());
        $data = ['categories' => $categories, 'produits' => $produits];

        return new JsonResponse($data);
    }

    #[Route(path: '/update/{id}/{quantite}')]
    public function updateQuantite(Produit $produit, int $quantite): JsonResponse
    {
        $produit->setQuantite($quantite);
        $produit->setUpdatedAt(new \DateTime());
        $this->produitRepository->flush();
        $data = ['quantite' => $quantite];
        $this->logger->log($produit, $quantite);

        return new JsonResponse($data);
    }
}
