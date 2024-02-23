<?php

namespace AcMarche\Travaux\Controller;

use AcMarche\Travaux\Entity\CategoryPlanning;
use AcMarche\Travaux\Form\CategoryPlanningType;
use AcMarche\Travaux\Repository\CategoryPlanningRepository;
use AcMarche\Travaux\Repository\EmployeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route(path: '/category/planning')]
#[IsGranted('ROLE_TRAVAUX_PLANNING')]
class CategoryPlanningController extends AbstractController
{
    public function __construct(
        private CategoryPlanningRepository $categoryPlanningRepository,
        private readonly EmployeRepository $employeRepository
    ) {
    }

    #[Route(path: '/', name: 'category_planning', methods: ['GET'])]
    public function index(): Response
    {
        $category_plannings = $this->categoryPlanningRepository->findAllOrdered();
        array_map(function (CategoryPlanning $categoryPlanning) {
            $categoryPlanning->ouriers = $this->employeRepository->findByCategory($categoryPlanning);
        }, $category_plannings);

        return $this->render(
            '@AcMarcheTravaux/category_planning/index.html.twig',
            array(
                'categories' => $category_plannings,
            )
        );
    }

    #[Route(path: '/new', name: 'category_planning_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $category_planning = new CategoryPlanning();
        $form = $this->createForm(CategoryPlanningType::class, $category_planning);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $this->categoryPlanningRepository->persist($category_planning);
            $this->categoryPlanningRepository->flush();

            $this->addFlash('success', 'La categorie bien été crée.');

            return $this->redirectToRoute('category_planning_show', array('id' => $category_planning->getId()));
        }

        return $this->render(
            '@AcMarcheTravaux/category_planning/new.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    #[Route(path: '/{id}', name: 'category_planning_show', methods: ['GET'])]
    public function show(CategoryPlanning $categoryPlanning): Response
    {
        $categoryPlanning->ouriers = $this->employeRepository->findByCategory($categoryPlanning);

        return $this->render(
            '@AcMarcheTravaux/category_planning/show.html.twig',
            array(
                'category' => $categoryPlanning,
            )
        );
    }

    #[Route(path: '/{id}/edit', name: 'category_planning_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CategoryPlanning $category_planning): Response
    {
        $editForm = $this->createForm(CategoryPlanningType::class, $category_planning);

        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->categoryPlanningRepository->flush();

            $this->addFlash('success', 'La catégorie a bien été modifiée.');

            return $this->redirectToRoute('category_planning_show', array('id' => $category_planning->getId()));
        }

        return $this->render(
            '@AcMarcheTravaux/category_planning/edit.html.twig',
            array(
                'category' => $category_planning,
                'form' => $editForm->createView(),
            )
        );
    }


    #[Route(path: '/{id}', name: 'category_planning_delete', methods: ['POST'])]
    public function delete(Request $request, CategoryPlanning $category_planning): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$category_planning->getId(), $request->request->get('_token'))) {

            $this->categoryPlanningRepository->remove($category_planning);
            $this->categoryPlanningRepository->flush();

            $this->addFlash('success', 'La catégorie a bien été supprimée.');
        }

        return $this->redirectToRoute('category_planning');
    }
}
