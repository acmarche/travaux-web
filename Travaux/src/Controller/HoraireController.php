<?php

namespace AcMarche\Travaux\Controller;

use AcMarche\Travaux\Entity\Horaire;
use AcMarche\Travaux\Form\HoraireType;
use AcMarche\Travaux\Repository\HoraireRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route(path: '/horaire')]
#[IsGranted('ROLE_TRAVAUX_PLANNING')]
class HoraireController extends AbstractController
{
    public function __construct(private HoraireRepository $horaireRepository, private readonly LoggerInterface $logger)
    {
    }

    #[Route(path: '/', name: 'horaire', methods: ['GET'])]
    public function index(): Response
    {
        $horaires = $this->horaireRepository->findAllOrdered();

        return $this->render(
            '@AcMarcheTravaux/horaire/index.html.twig',
            array(
                'horaires' => $horaires,
            )
        );
    }

    #[Route(path: '/new', name: 'horaire_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $horaire = new Horaire();
        $form = $this->createForm(HoraireType::class, $horaire);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $this->horaireRepository->persist($horaire);
            $this->horaireRepository->flush();

            $this->addFlash('success', 'L\'horaire bien été créé.');

            return $this->redirectToRoute('horaire_show', array('id' => $horaire->getId()));
        }

        return $this->render(
            '@AcMarcheTravaux/horaire/new.html.twig',
            array(
                'form' => $form,
            )
        );
    }

    #[Route(path: '/{id}', name: 'horaire_show', methods: ['GET'])]
    public function show(Horaire $horaire): Response
    {
        return $this->render(
            '@AcMarcheTravaux/horaire/show.html.twig',
            array(
                'horaire' => $horaire,
            )
        );
    }

    #[Route(path: '/{id}/edit', name: 'horaire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Horaire $horaire): Response
    {
        $editForm = $this->createForm(HoraireType::class, $horaire);

        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->horaireRepository->flush();

            $this->addFlash('success', 'L\'horaire a bien été modifié.');

            return $this->redirectToRoute('horaire_show', array('id' => $horaire->getId()));
        }

        return $this->render(
            '@AcMarcheTravaux/horaire/edit.html.twig',
            array(
                'horaire' => $horaire,
                'form' => $editForm->createView(),
            )
        );
    }


    #[Route(path: '/{id}', name: 'horaire_delete', methods: ['POST'])]
    public function delete(Request $request, Horaire $horaire): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$horaire->getId(), $request->request->get('_token'))) {

            $this->horaireRepository->remove($horaire);
            $this->horaireRepository->flush();

            $this->addFlash('success', 'L\'horaire a bien été supprimé.');
        }

        return $this->redirectToRoute('horaire');
    }

    #[Route(path: '/update/position/{id}', name: 'horaire_position', methods: ['PATCH', 'GET'])]
    public function sort(Request $request, Horaire $horaire): Response
    {
        $data = $request->getContent();

        $regex = '#"position"\s*(\d)#';
        preg_match($regex, $data, $matches);

        $this->logger->debug('ZEZE'.$matches[1]);

        $position = (int)$matches[1];
        if ($position) {
            $horaire->position = $position;
            $this->horaireRepository->flush();
        }

        return new Response($position);
    }
    /**
     * ------WebKitFormBoundaryAtKCeJaz10WSdUfU
     * Content-Disposition: form-data; name="position"
     *
     * 2
     * ------WebKitFormBoundaryAtKCeJaz10WSdUfU--
     */
}
