<?php

namespace AcMarche\Avaloir\Controller;

use AcMarche\Avaloir\Entity\Quartier;
use AcMarche\Avaloir\Form\QuartierRueType;
use AcMarche\Avaloir\Repository\QuartierRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route(path: '/quartier/rue')]
#[IsGranted('ROLE_TRAVAUX_AVALOIR')]
class QuartierRueController extends AbstractController
{
    public function __construct(private QuartierRepository $quartierRepository)
    {
    }

    #[Route(path: '/new/{id}', name: 'quartierrue_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Quartier $quartier): Response
    {
        $form = $this->createForm(QuartierRueType::class, $quartier);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $this->quartierRepository->flush();

            $this->addFlash('success', 'Les rues ont bien modifiées.');

            return $this->redirectToRoute('quartier_show', array('id' => $quartier->getId()));
        }

        return $this->render('@AcMarcheAvaloir/quartier_rue/new.html.twig', array(
            'quartier' => $quartier,
            'form' => $form->createView(),
        ));
    }
}
