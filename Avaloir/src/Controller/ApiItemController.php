<?php

namespace AcMarche\Avaloir\Controller;

use AcMarche\Avaloir\Entity\Item;
use AcMarche\Avaloir\MailerAvaloir;
use AcMarche\Avaloir\Repository\ItemRepository;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;

#[Route(path: '/items/api')]
class ApiItemController extends AbstractController
{
    public function __construct(
        private readonly ItemRepository $itemRepository,
        private readonly MailerAvaloir $mailerAvaloir,
        private readonly CacheInterface $cache,
        private readonly LoggerInterface $logger
    ) {
    }

    #[Route(path: '/categories', methods: ['GET'], format: 'json')]
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

    #[Route(path: '/insert', methods: ['POST'], format: 'json')]
    public function insert(Request $request): JsonResponse
    {
        $coordinatesJson = $request->request->get('coordinates');
        try {
            $data = json_decode($coordinatesJson, true, 512, JSON_THROW_ON_ERROR);
            if (!$item = $this->itemRepository->findByLatitudeAndLongitude(
                $data['latitude'],
                $data['longitude']
            )) {
                $item = new Item();
                $item->latitude = $data['latitude'];
                $item->longitude = $data['longitude'];
                $this->itemRepository->persist($item);
            }
            if (isset($data['createdAt'])) {
                $date = $data['createdAt'];
                $dateTime = false;
                try {
                    $dateTime = \DateTime::createFromFormat("Y-m-d H:m", $date);
                } catch (\Exception $exception) {
                    $this->logger->log(LogLevel::ERROR, 'error '.$exception->getMessage());
                }
                if (!$dateTime) {
                    $dateTime = new \DateTime();
                }
                $item->setCreatedAt($dateTime);
                $item->setUpdatedAt($dateTime);
            }
            $this->itemRepository->flush();

        } catch (\Exception $exception) {
            $data = [
                'error' => 1,
                'message' => 'Avaloir non insérer dans la base de données',
                'avaloir' => $exception->getMessage(),
            ];

            $this->logger->log(LogLevel::ERROR, 'error '.$exception->getMessage());
            $this->mailerAvaloir->sendError('Item non insérer dans la base de données', $data);

            return new JsonResponse($data);
        }
        $result = $this->uploadImage($item, $request);
        if (isset($result['error']) && $result['error'] > 0) {
            $this->logger->log(LogLevel::ERROR, 'error upload image '.$result['error']);
            $this->mailerAvaloir->sendError('image upload error', $result);

            return new JsonResponse($result);
        }

        try {
            //  $this->meiliServer->addData($item);
        } catch (\Exception $exception) {
            $this->mailerAvaloir->sendError('update search avaloir', [$exception->getMessage()]);
        }


        $data = [
            'error' => 0,
            'elastic' => '',
            'message' => 'ok',
            'avaloir' => $this->serializeApi($item),
        ];

        return new JsonResponse($data);
    }

    public function uploadImage(Item $item, Request $request): array
    {
        /**
         * @var UploadedFile $image
         */
        $image = $request->files->get('image');

        if (!$image instanceof UploadedFile) {
            return
                [
                    'error' => 1,
                    'message' => 'Upload raté',
                    'avaloir' => $this->serializeApi($item),
                ];
        }

        if ($image->getError() !== 0) {
            return
                [
                    'error' => 1,
                    'message' => $image->getErrorMessage(),
                    'avaloir' => $this->serializeApi($item),
                ];
        }

        $this->upload($item, $image);

        return [];
    }

    private function upload(Item $item, UploadedFile $image): array
    {
        $name = 'aval-'.$item->id.'.jpg';
        try {
            $image->move(
                $this->getParameter('ac_marche_avaloir.upload.directory').DIRECTORY_SEPARATOR.$item->id,
                $name
            );
        } catch (FileException) {
            return [
                'error' => 1,
                'message' => $image->getErrorMessage(),
                'avaloir' => $this->serializeApi($item),
            ];
        }

        $item->imageName = $name;
        $this->itemRepository->flush();

        return ['error' => 0, 'message' => $name, 'avaloir' => $this->serializeApi($item)];
    }

    private function serializeApi(Item $item): array
    {
        return [
            'id' => $item->id,
            'refId' => $item->id,
            'latitude' => $item->latitude,
            'longitude' => $item->longitude,
        ];

    }
}
