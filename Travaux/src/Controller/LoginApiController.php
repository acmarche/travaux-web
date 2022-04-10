<?php
/**
 * Created by PhpStorm.
 * User: jfsenechal
 * Date: 17/01/19
 * Time: 10:38
 */

namespace AcMarche\Travaux\Controller;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Response;
use AcMarche\Stock\Service\SerializeApi;
use AcMarche\Travaux\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController
 * @package AcMarche\Mercredi\Api\Controller
 */
#[Route(path: '/logapi')]
class LoginApiController extends AbstractController
{
    public function __construct(private UserRepository $userRepository, private SerializeApi $serializer, private UserPasswordHasherInterface $userPasswordEncoder)
    {
    }
    #[Route(path: '/', methods: ['POST'])]
    public function login(Request $request) : JsonResponse
    {
        $error = [];
        $username = $request->request->get('username');
        $password = $request->request->get('password');
        if (!$username || !$password) {

            $error['message'] = 'Champs non remplis';

            return new JsonResponse($error, Response::HTTP_UNAUTHORIZED);
        }
        $user = $this->userRepository->findOneBy(['username' => $username]);
        if ($user !== null) {
            if ($this->userPasswordEncoder->isPasswordValid($user, $password)) {

                $user = $this->serializer->serializeUser($user);
                $this->userRepository->save();

                return new JsonResponse($user);
            }

            $error['message'] = 'Mauvais mot de passe';

            return new JsonResponse($error, Response::HTTP_UNAUTHORIZED);

        }
        $error['message'] = 'Utilisateur non trouvé';
        return new JsonResponse($error, Response::HTTP_UNAUTHORIZED);
    }
}