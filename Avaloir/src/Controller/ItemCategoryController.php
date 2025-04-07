<?php


namespace AcMarche\Avaloir\Controller;

use AcMarche\Avaloir\Repository\ItemCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/item/category')]
class ItemCategoryController extends AbstractController
{
    public function __construct(private readonly ItemCategoryRepository $itemCategoryRepository)
    {
    }

    #[Route(path: '/', name: 'item_category_index', methods: ['GET'])]
    public function index(): Response
    {
        $categories = $this->itemCategoryRepository->findAll();

        return $this->render('@AcMarcheAvaloir/item_category/index.html.twig', ['categories' => $categories]);
    }
}
