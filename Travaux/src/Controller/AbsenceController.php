<?php

namespace AcMarche\Travaux\Controller;

use AcMarche\Travaux\Entity\Absence;
use AcMarche\Travaux\Entity\Employe;
use AcMarche\Travaux\Form\AbsenceType;
use AcMarche\Travaux\Repository\AbsenceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/absence')]
#[IsGranted('ROLE_TRAVAUX_PLANNING')]
class AbsenceController extends AbstractController
{
    public function __construct(private AbsenceRepository $absenceRepository)
    {
    }

    #[Route(path: '/index/{id}', name: 'absence_index', methods: ['GET'])]
    public function index(Employe $employe): Response
    {
        $absences = $this->absenceRepository->findByEmploye($employe);

        return $this->render(
            '@AcMarcheTravaux/absence/index.html.twig',
            array(
                'absences' => $absences,
                'employe' => $employe,
            )
        );
    }

    #[Route(path: '/new/{id}', name: 'absence_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Employe $employe): Response
    {
        $absence = new Absence($employe);
        $form = $this->createForm(AbsenceType::class, $absence);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->absenceRepository->persist($absence);
            $this->absenceRepository->flush();

            $this->addFlash('success', 'L\'absence a bien été crée.');

            return $this->redirectToRoute('absence_show', array('id' => $absence->getId()));
        }

        return $this->render(
            '@AcMarcheTravaux/absence/new.html.twig',
            array(
                'absence' => $absence,
                'employe' => $employe,
                'form' => $form->createView(),
            )
        );
    }

    #[Route(path: '/show/{id}', name: 'absence_show', methods: ['GET'])]
    public function show(Absence $absence): Response
    {
        $employe = $absence->employe;

        return $this->render(
            '@AcMarcheTravaux/absence/show.html.twig',
            array(
                'absence' => $absence,
                'employe' => $employe,
            )
        );
    }

    #[Route(path: '/{id}/edit', name: 'absence_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Absence $absence): Response
    {
        $form = $this->createForm(AbsenceType::class, $absence);
        $employe = $absence->employe;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->absenceRepository->flush();

            $this->addFlash('success', 'L\'absence a bien été modifiée.');

            return $this->redirectToRoute('absence_show', array('id' => $absence->getId()));
        }

        return $this->render(
            '@AcMarcheTravaux/absence/edit.html.twig',
            array(
                'absence' => $absence,
                'employe' => $employe,
                'form' => $form->createView(),
            )
        );
    }

    #[Route(path: '/{id}', name: 'absence_delete', methods: ['POST'])]
    public function delete(Request $request, Absence $absence): RedirectResponse
    {
        $employe = $absence->employe;
        if ($this->isCsrfTokenValid('delete'.$absence->getId(), $request->request->get('_token'))) {


            $this->absenceRepository->remove($absence);
            $this->absenceRepository->flush();

            $this->addFlash('success', 'L\'absence a bien été supprimée.');
        }

        return $this->redirectToRoute('employe_show', ['id' => $employe->getId()]);
    }
}
