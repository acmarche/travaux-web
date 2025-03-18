<?php

namespace AcMarche\Avaloir\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/items/api')]
class ApiItemController extends AbstractController
{
    public function __construct()
    {
    }

    #[Route(path: '/categories', format: 'json', methods: ['GET'])]
    public function categories(): JsonResponse
    {
        $items = [
            [
                "name" => "Bancs",
                "id" => 1,
                "image" => "https://apptravaux.marche.be/bundles/travaux/images/items/picnic-table.png",
            ],
            [
                "name" => "Feu de circulation",
                "id" => 2,
                "image" => "https://apptravaux.marche.be/bundles/travaux/images/items/traffic-lights.png",
            ],
            [
                "name" => "Passage pour piétons",
                "id" => 3,
                "image" => "https://apptravaux.marche.be/bundles/travaux/images/items/trash.png",
            ],
            [
                "name" => "Poubelles",
                "id" => 4,
                "image" => "https://apptravaux.marche.be/bundles/travaux/images/items/walk.png",
            ],
        ];

        return $this->json($items);

    }

}
