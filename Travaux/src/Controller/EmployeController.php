<?php

namespace AcMarche\Travaux\Controller;

use AcMarche\Travaux\Entity\Employe;
use AcMarche\Travaux\Form\EmployeType;
use AcMarche\Travaux\Repository\AbsenceRepository;
use AcMarche\Travaux\Repository\EmployeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route(path: '/employe')]
#[IsGranted('ROLE_TRAVAUX_PLANNING')]
class EmployeController extends AbstractController
{
    public function __construct(
        private EmployeRepository $employeRepository,
        private AbsenceRepository $absenceRepository
    ) {
    }

    #[Route(path: '/', name: 'employe', methods: ['GET'])]
    public function index(): Response
    {
        $employes = $this->employeRepository->findAllOrdered();

        return $this->render(
            '@AcMarcheTravaux/employe/index.html.twig',
            array(
                'employes' => $employes,
            )
        );
    }

    #[Route(path: '/new', name: 'employe_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $employe = new Employe();
        $form = $this->createForm(EmployeType::class, $employe);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $this->employeRepository->persist($employe);
            $this->employeRepository->flush();

            $this->addFlash('success', 'L\'employé a bien été créé.');

            return $this->redirectToRoute('employe_show', array('id' => $employe->getId()));
        }

        return $this->render(
            '@AcMarcheTravaux/employe/new.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    #[Route(path: '/{id}/show', name: 'employe_show', methods: ['GET'])]
    public function show(Employe $employe): Response
    {
        $absences = $this->absenceRepository->findByEmploye($employe);

        return $this->render(
            '@AcMarcheTravaux/employe/show.html.twig',
            array(
                'employe' => $employe,
                'absences' => $absences,
            )
        );
    }

    #[Route(path: '/{id}/edit', name: 'employe_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Employe $employe): Response
    {
        $editForm = $this->createForm(EmployeType::class, $employe);

        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->employeRepository->flush();

            $this->addFlash('success', 'L\'employé a bien été modifié.');

            return $this->redirectToRoute('employe_show', array('id' => $employe->getId()));
        }

        return $this->render(
            '@AcMarcheTravaux/employe/edit.html.twig',
            array(
                'employe' => $employe,
                'form' => $editForm->createView(),
            )
        );
    }


    #[Route(path: '/{id}', name: 'employe_delete', methods: ['POST'])]
    public function delete(Request $request, Employe $employe): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$employe->getId(), $request->request->get('_token'))) {

            $this->employeRepository->remove($employe);
            $this->employeRepository->flush();

            $this->addFlash('success', 'L\'employé a bien été supprimé.');
        }

        return $this->redirectToRoute('employe');
    }
}
