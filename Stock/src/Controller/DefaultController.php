<?php


namespace AcMarche\Stock\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class DefaultController
 * @package AcMarche\Stock\Controller
 *
 */
class DefaultController extends AbstractController
{
    #[Route(path: '/', name: 'stock_home')]
    #[IsGranted('ROLE_TRAVAUX_STOCK')]
    public function index() : Response
    {
        return $this->redirectToRoute('stock_produit_index');
    }
}
