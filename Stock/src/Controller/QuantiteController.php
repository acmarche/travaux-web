<?php


namespace AcMarche\Stock\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use AcMarche\Stock\Entity\Produit;
use AcMarche\Stock\Form\QuantiteType;
use AcMarche\Stock\Service\Logger;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController
 * @package AcMarche\Stock\Controller
 */
#[Route(path: '/quantite')]
#[IsGranted('ROLE_TRAVAUX_STOCK')]
class QuantiteController extends AbstractController
{
    public function __construct(private Logger $logger, private ManagerRegistry $managerRegistry)
    {
    }
    #[Route(path: '/{id}', name: 'stock_quantite_update')]
    public function index(Request $request, Produit $produit) : Response
    {
        $form = $this->createForm(QuantiteType::class, $produit);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->logger->log($produit, $form->getData()->getQuantite());

            $entityManager = $this->managerRegistry->getManager();
            $entityManager->flush();
            $this->addFlash('success', 'Les quantités ont bien été mise à jour.');

            return $this->redirectToRoute('stock_produit_show', ['id' => $produit->getId()]);
        }
        return $this->render(
            '@AcMarcheStock/quantite/index.html.twig',
            ['produit' => $produit, 'form' => $form->createView()]
        );
    }
}
