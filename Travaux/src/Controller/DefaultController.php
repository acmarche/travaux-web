<?php

namespace AcMarche\Travaux\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class DefaultController extends AbstractController
{
    #[Route(path: '/', name: 'homepage')]
    public function index(): RedirectResponse
    {
        if ($this->isGranted("ROLE_TRAVAUX")) {
            return $this->redirectToRoute('intervention', array(), '301');
        }

        return $this->redirectToRoute('app_login');
    }

    #[Route(path: '/documentation', name: 'documentation')]
    #[IsGranted('ROLE_TRAVAUX')]
    public function documentation(): Response
    {
        return $this->render('@AcMarcheTravaux/default/documentation.html.twig');
    }
}
