<?php

namespace AcMarche\Avaloir\Controller;

use AcMarche\Avaloir\Entity\Avaloir;
use AcMarche\Avaloir\Entity\Commentaire;
use AcMarche\Avaloir\Entity\DateNettoyage;
use AcMarche\Avaloir\Location\LocationMath;
use AcMarche\Avaloir\Location\LocationUpdater;
use AcMarche\Avaloir\MailerAvaloir;
use AcMarche\Avaloir\Repository\AvaloirRepository;
use AcMarche\Avaloir\Repository\CommentaireRepository;
use AcMarche\Avaloir\Repository\DateNettoyageRepository;
use AcMarche\Stock\Service\SerializeApi;
use AcMarche\Travaux\Search\MeiliServer;
use AcMarche\Travaux\Search\SearchMeili;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;

#[Route(path: '/api')]
class ApiController extends AbstractController
{
    public function __construct(
        private AvaloirRepository $avaloirRepository,
        private DateNettoyageRepository $dateNettoyageRepository,
        private CommentaireRepository $commentaireRepository,
        private SerializeApi $serializeApi,
        private SearchMeili $meilisearch,
        private MeiliServer $meiliServer,
        private MailerAvaloir $mailerAvaloir,
        private LocationUpdater $locationUpdater,
        private CacheInterface $cache,
        private LocationMath $locationMath,
    ) {
    }

    #[Route(path: '/all', format: 'json')]
    public function index(): Response
    {
        $date = "";
        $last = $this->avaloirRepository->getLastUpdatedAvaloir();

        if ($last) {
            $date = $last->getUpdatedAt()->format('Y-m-d H');
        }

        return $this->cache->get('allAvaloirs-'.$date, function () {
            $avaloirs = $this->serializeApi->serializeAvaloirs($this->avaloirRepository->getAll());

            return new JsonResponse($avaloirs);
        });
    }

    #[Route(path: '/dates', format: 'json')]
    public function dates(): JsonResponse
    {
        $dates = $this->serializeApi->serializeDates($this->dateNettoyageRepository->findForNew());

        return new JsonResponse($dates);
    }

    #[Route(path: '/commentaires', format: 'json')]
    public function commentaires(): JsonResponse
    {
        $commentaires = $this->serializeApi->serializeCommentaires($this->commentaireRepository->findAll());

        return new JsonResponse($commentaires);
    }

    #[Route(path: '/insert', format: 'json')]
    public function insert(Request $request): JsonResponse
    {
        $coordinatesJson = $request->request->get('coordinates');
        try {
            $data = json_decode($coordinatesJson, true, 512, JSON_THROW_ON_ERROR);
            $avaloir = new Avaloir();
            $avaloir->setLatitude($data['latitude']);
            $avaloir->setLongitude($data['longitude']);
            if (isset($data['createdAt'])) {
                $date = $data['createdAt'];
                $dateTime = false;
                try {
                    $dateTime = DateTime::createFromFormat("Y-m-d H:m", $date);
                } catch (Exception $exception) {

                }
                if (!$dateTime) {
                    $dateTime = new DateTime();
                }
                $avaloir->setCreatedAt($dateTime);
                $avaloir->setUpdatedAt($dateTime);
            }
            $this->locationMath->calculate($avaloir);
            $this->avaloirRepository->persist($avaloir);
            $this->avaloirRepository->flush();
        } catch (Exception $exception) {
            $data = [
                'error' => 1,
                'message' => 'Avaloir non insérer dans la base de données',
                'avaloir' => $exception->getMessage(),
            ];

            $this->mailerAvaloir->sendError('Avaloir non insérer dans la base de données', $data);

            return new JsonResponse($data);
        }

        $dateNettoyage = new DateNettoyage();
        $dateNettoyage->setAvaloir($avaloir);
        $dateNettoyage->setJour(new \DateTime());
        $this->dateNettoyageRepository->persist($dateNettoyage);
        $this->dateNettoyageRepository->flush();

        $this->locationUpdater->updateRueAndLocalite($avaloir);
        $result = $this->uploadImage($avaloir, $request);
        if ($result['error'] > 0) {
            $this->mailerAvaloir->sendError('image upload error', $result);

            return new JsonResponse($result);
        }
        try {
            $result = $this->meiliServer->addData($avaloir);
            $data = [
                'error' => 0,
                'elastic' => $result,
                'message' => 'ok',
                'avaloir' => $this->serializeApi->serializeAvaloir($avaloir),
            ];

            return new JsonResponse($data);
        } catch (Exception $e) {
            $data = [
                'error' => 1,
                'message' => $e->getMessage(),
                'avaloir' => $this->serializeApi->serializeAvaloir($avaloir),
            ];

            return new JsonResponse($data);
        }
    }

    #[Route(path: '/update/{id}', format: 'json')]
    public function update(int $id, Request $request): JsonResponse
    {
        $avaloir = $this->avaloirRepository->find($id);
        if ($avaloir === null) {
            $data = [
                'error' => 404,
                'message' => "Avaloir non trouvé",
                'avaloir' => null,
            ];

            $this->mailerAvaloir->sendError('Update - Avaloir non trouvé', $data);

            return new JsonResponse($data);
        }
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->avaloirRepository->persist($avaloir);
        $data = ['error' => 0, 'message' => $data, 'avaloir' => $this->serializeApi->serializeAvaloir($data)];

        return new JsonResponse($data);
    }

    #[Route(path: '/clean/{id}/{dateString}', format: 'json')]
    public function addCleaning(int $id, string $dateString): JsonResponse
    {
        $avaloir = $this->avaloirRepository->find($id);
        if ($avaloir === null) {
            $data = [
                'error' => 404,
                'message' => "Avaloir non trouvé",
                'avaloir' => null,
            ];

            $this->mailerAvaloir->sendError('avaloir non trouvé, add clean date', $data);

            return new JsonResponse($data);
        }
        $date = DateTime::createFromFormat('Y-m-d', $dateString);
        if (($dateNettoyage = $this->dateNettoyageRepository->findOneBy(['avaloir' => $avaloir, 'jour' => $date]
            )) !== null) {

            $data = ['error' => 0, 'message' => "ok", 'date' => $this->serializeApi->serializeDate($dateNettoyage)];

            return new JsonResponse($data);
        }
        $dateNettoyage = new DateNettoyage();
        $this->dateNettoyageRepository->persist($dateNettoyage);
        $dateNettoyage->setAvaloir($avaloir);
        $dateNettoyage->setJour($date);
        $dateNettoyage->setUpdatedAt($date);
        $dateNettoyage->setCreatedAt($date);
        $avaloir->addDate($dateNettoyage);
        $this->dateNettoyageRepository->flush();
        $data = ['error' => 0, 'message' => "ok", 'date' => $this->serializeApi->serializeDate($dateNettoyage)];

        return new JsonResponse($data);
    }

    #[Route(path: '/commentaire/{id}/{content}', format: 'json')]
    public function addComment(int $id, string $content): JsonResponse
    {
        $avaloir = $this->avaloirRepository->find($id);
        if ($avaloir === null) {
            $data = [
                'error' => 404,
                'message' => "Avaloir non trouvé",
                'avaloir' => null,
            ];

            $this->mailerAvaloir->sendError("Comment, Avaloir non trouvé", $data);

            return new JsonResponse($data);
        }
        $commentaire = new Commentaire($avaloir);
        $commentaire->setContent($content);
        $this->commentaireRepository->persist($commentaire);
        $this->commentaireRepository->flush();
        $data = [
            'error' => 0,
            'message' => "ok",
            'commentaire' => $this->serializeApi->serializeCommentaire($commentaire),
        ];

        return new JsonResponse($data);
    }

    public function uploadImage(Avaloir $avaloir, Request $request): array
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
                    'avaloir' => $this->serializeApi->serializeAvaloir($avaloir),
                ];
        }

        if ($image->getError() !== 0) {
            return
                [
                    'error' => 1,
                    'message' => $image->getErrorMessage(),
                    'avaloir' => $this->serializeApi->serializeAvaloir($avaloir),
                ];
        }

        $this->upload($avaloir, $image);

        return [];
    }

    private function upload(Avaloir $avaloir, UploadedFile $image): array
    {
        $name = 'aval-'.$avaloir->getId().'.jpg';
        try {
            $image->move(
                $this->getParameter('ac_marche_avaloir.upload.directory').DIRECTORY_SEPARATOR.$avaloir->getId(),
                $name
            );
        } catch (FileException) {
            return [
                'error' => 1,
                'message' => $image->getErrorMessage(),
                'avaloir' => $this->serializeApi->serializeAvaloir($avaloir),
            ];
        }

        $avaloir->setImageName($name);
        $this->avaloirRepository->flush();

        return ['error' => 0, 'message' => $name, 'avaloir' => $this->serializeApi->serializeAvaloir($avaloir)];
    }

    #[Route(path: '/search', format: 'json')]
    public function search(Request $request): JsonResponse
    {
        $this->mailerAvaloir->sendError('search avaloir', ['ok']);
        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return new JsonResponse(
                [
                    'error' => 1,
                    'message' => 'idi '.$e->getMessage().$request->getContent(),
                    'avaloirs' => [],
                ]
            );
        }

        $latitude = $data['latitude'];
        $longitude = $data['longitude'];
        $distance = (int)$data['distance'];
        $t = ['distance' => $distance, 'latitude' => $latitude, 'longitude' => $longitude];
        $this->mailerAvaloir->sendError('search avaloir', $t);

        if (!$latitude || !$longitude || !$distance) {
            return new JsonResponse(
                [
                    'error' => 1,
                    'message' => 'Latitude, longitude et distance obligatoire',
                    'avaloirs' => [],
                ]
            );
        }

        try {
            $result = $this->meilisearch->searchGeo($latitude, $longitude, $distance);
            $hits = $result->getHits();
            $total = $result->count();
        } catch (Exception $exception) {

            $this->mailerAvaloir->sendError('search avaloir', [$exception->getMessage()]);

            return new JsonResponse(
                [
                    'error' => 1,
                    'message' => $exception->getMessage(),
                    'avaloirs' => [],
                ]
            );

        }
        $avaloirs = [];
        $t = ['distance' => $distance, 'latitude' => $latitude, 'longitude' => $longitude];
        $this->mailerAvaloir->sendError('search avaloir', $t);

        foreach ($hits as $post) {
            $id = $post['id'];
            if (($avaloir = $this->avaloirRepository->find($id)) !== null) {
                $avaloirs[] = $this->serializeApi->serializeAvaloir($avaloir);
            }
        }

        return new JsonResponse(
            [
                'error' => 0,
                'message' => 'distance: '.$distance.' latitude: '.$latitude.' longitude: '.$longitude.'ok count '.$total,
                'avaloirs' => $avaloirs,
            ]
        );
    }
}
