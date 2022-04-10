<?php


namespace AcMarche\Avaloir\Controller;

use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    #[IsGranted('ROLE_TRAVAUX_AVALOIR')]
    public function index() : Response
    {
        return $this->render('@AcMarcheAvaloir/default/index.html.twig');
    }
}
