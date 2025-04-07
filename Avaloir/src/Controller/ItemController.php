<?php


namespace AcMarche\Avaloir\Controller;

use AcMarche\Avaloir\Repository\ItemRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route(path: '/items')]
class ItemController extends AbstractController
{
    public function __construct(private readonly ItemRepository $itemRepository)
    {
    }

    #[Route(path: '/', name: 'item_index', methods: ['GET'])]
    public function index(): Response
    {
        $items = $this->itemRepository->findAll();

        return $this->render('@AcMarcheAvaloir/item/index.html.twig', ['items' => $items]);
    }
}
