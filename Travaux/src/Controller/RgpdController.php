<?php

namespace AcMarche\Travaux\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use AcMarche\Travaux\Entity\Security\Group;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/rgpd')]
#[IsGranted('ROLE_TRAVAUX')]
class RgpdController extends AbstractController
{
    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }
    #[Route(path: '/', name: 'rgpd')]
    public function index() : Response
    {
        $em = $this->managerRegistry->getManager();
        $groupes = $em->getRepository(Group::class)->findAll();
        return $this->render('rgpd/index.html.twig', ['groupes' => $groupes]);
    }
}
