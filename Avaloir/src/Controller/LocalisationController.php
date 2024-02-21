<?php


namespace AcMarche\Avaloir\Controller;

use AcMarche\Avaloir\Entity\Avaloir;
use AcMarche\Avaloir\Form\LocalisationType;
use AcMarche\Avaloir\Location\LocationUpdater;
use AcMarche\Avaloir\Repository\AvaloirRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/localisation')]
class LocalisationController extends AbstractController
{
    public function __construct(private AvaloirRepository $avaloirRepository, private LocationUpdater $locationUpdater)
    {
    }

    #[IsGranted('ROLE_TRAVAUX_AVALOIR')]
    #[Route(path: '/{id}', name: 'avaloir_localisation_update', methods: ['POST'])]
    public function update(Request $request, Avaloir $avaloir): RedirectResponse
    {
        $form = $this->createForm(LocalisationType::class, $avaloir);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->locationUpdater->updateRueAndLocalite($avaloir);
                $this->avaloirRepository->flush();
                $this->addFlash("success", "La situation a bien été modifiée");
            } catch (\Exception $exception) {
                $this->addFlash("danger", $exception->getMessage());
            }
        }

        return $this->redirectToRoute('avaloir_show', ['id' => $avaloir->getId()]);
    }
}
