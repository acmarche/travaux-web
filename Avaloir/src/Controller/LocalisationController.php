<?php


namespace AcMarche\Avaloir\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use AcMarche\Avaloir\Entity\Avaloir;
use AcMarche\Avaloir\Form\LocalisationType;
use AcMarche\Avaloir\Location\LocationUpdater;
use AcMarche\Avaloir\Repository\AvaloirRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/localisation')]
class LocalisationController extends AbstractController
{
    public function __construct(private AvaloirRepository $avaloirRepository, private LocationUpdater $locationUpdater)
    {
    }
    #[IsGranted('ROLE_TRAVAUX_AVALOIR')]
    #[Route(path: '/{id}', name: 'avaloir_localisation_update', methods: ['POST'])]
    public function update(Request $request, Avaloir $avaloir) : RedirectResponse
    {
        $form = $this->createForm(LocalisationType::class, $avaloir);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->locationUpdater->updateRueAndLocalite($avaloir);
            $this->avaloirRepository->flush();
            $this->addFlash("success", "La situation a bien été modifiée");
        }
        return $this->redirectToRoute('avaloir_show', ['id' => $avaloir->getId()]);
    }
}
