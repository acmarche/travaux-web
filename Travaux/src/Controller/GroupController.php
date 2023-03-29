<?php

namespace AcMarche\Travaux\Controller;

use AcMarche\Travaux\Entity\Security\Group;
use AcMarche\Travaux\Form\Security\GroupType;
use AcMarche\Travaux\Repository\GroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route(path: '/group')]
#[IsGranted('ROLE_TRAVAUX_ADMIN')]
class GroupController extends AbstractController
{
    public function __construct(private GroupRepository $groupRepository)
    {
    }

    #[Route(path: '/', name: 'group', methods: ['GET'])]
    public function index(): Response
    {
        $groups = $this->groupRepository->findAll();

        return $this->render(
            '@AcMarcheTravaux/group/index.html.twig',
            array(
                'groups' => $groups,
            )
        );
    }

    #[Route(path: '/new', name: 'group_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $group = new Group('');
        $form = $this->createForm(GroupType::class, $group);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $this->groupRepository->persist($group);
            $this->groupRepository->flush();

            $this->addFlash('success', 'Le groupe a bien été créé.');

            return $this->redirectToRoute('group_show', array('id' => $group->getId()));
        }

        return $this->render(
            '@AcMarcheTravaux/group/new.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    #[Route(path: '/{id}', name: 'group_show', methods: ['GET'])]
    public function show(Group $group): Response
    {
        return $this->render(
            '@AcMarcheTravaux/group/show.html.twig',
            array(
                'group' => $group,
            )
        );
    }

    #[Route(path: '/{id}/edit', name: 'group_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Group $group): Response
    {
        $group->addRole('');
        $editForm = $this->createForm(GroupType::class, $group);

        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->groupRepository->flush();

            $this->addFlash('success', 'Le groupe a bien été modifié.');

            return $this->redirectToRoute('group_show', array('id' => $group->getId()));
        }

        return $this->render(
            '@AcMarcheTravaux/group/edit.html.twig',
            array(
                'group' => $group,
                'form' => $editForm->createView(),
            )
        );
    }


    #[Route(path: '/{id}', name: 'group_delete', methods: ['POST'])]
    public function delete(Request $request, Group $group): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$group->getId(), $request->request->get('_token'))) {

            $this->groupRepository->remove($group);
            $this->groupRepository->flush();

            $this->addFlash('success', 'Le groupe a bien été supprimé.');
        }

        return $this->redirectToRoute('group');
    }
}
