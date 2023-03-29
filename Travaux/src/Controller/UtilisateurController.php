<?php

namespace AcMarche\Travaux\Controller;

use AcMarche\Travaux\Entity\Security\User;
use AcMarche\Travaux\Form\Security\UtilisateurEditType;
use AcMarche\Travaux\Form\Security\UtilisateurPasswordType;
use AcMarche\Travaux\Form\Security\UtilisateurType;
use AcMarche\Travaux\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/utilisateur')]
#[IsGranted('ROLE_TRAVAUX_ADMIN')]
class UtilisateurController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $userPasswordHasher,
        private ManagerRegistry $managerRegistry
    ) {
    }

    #[Route(path: '/', name: 'actravaux_utilisateur', methods: ['GET'])]
    public function index(): Response
    {
        $users = $this->userRepository->findBy([], ['nom' => 'ASC']);

        return $this->render(
            '@AcMarcheTravaux/utilisateur/index.html.twig',
            array(
                'users' => $users,
            )
        );
    }

    #[Route(path: '/new', name: 'actravaux_utilisateur_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $utilisateur = new User();
        $form = $this->createForm(UtilisateurType::class, $utilisateur)
            ->add('submit', SubmitType::class, array('label' => 'Create'));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $utilisateur->setPassword(
                $this->userPasswordHasher->hashPassword($utilisateur, $form->getData()->getPlainPassword())
            );
            $this->userRepository->insert($utilisateur);

            $this->addFlash("success", "L'utilisateur a bien été ajouté");

            return $this->redirectToRoute('actravaux_utilisateur');
        }

        return $this->render(
            '@AcMarcheTravaux/utilisateur/new.html.twig',
            array(
                'utilisateur' => $utilisateur,
                'form' => $form->createView(),
            )
        );
    }

    #[Route(path: '/{id}', name: 'actravaux_utilisateur_show', methods: ['GET'])]
    public function show(User $utilisateur): Response
    {
        return $this->render(
            '@AcMarcheTravaux/utilisateur/show.html.twig',
            array(
                'utilisateur' => $utilisateur,
            )
        );
    }

    #[Route(path: '/{id}/edit', name: 'actravaux_utilisateur_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $utilisateur): Response
    {
        $editForm = $this->createForm(UtilisateurEditType::class, $utilisateur)
            ->add('submit', SubmitType::class, array('label' => 'Update'));

        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->userRepository->save();
            $this->addFlash("success", "L'utilisateur a bien été modifié");

            return $this->redirectToRoute('actravaux_utilisateur');
        }

        return $this->render(
            '@AcMarcheTravaux/utilisateur/edit.html.twig',
            array(
                'utilisateur' => $utilisateur,
                'edit_form' => $editForm->createView(),
            )
        );
    }

    #[Route(path: '/{id}', name: 'actravaux_utilisateur_delete', methods: ['POST'])]
    public function delete(Request $request, User $user): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->managerRegistry->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', 'L\'utilisateur a été supprimé');
        }

        return $this->redirectToRoute('actravaux_utilisateur');
    }

    #[Route(path: '/password/{id}', name: 'actravaux_utilisateur_password', methods: ['GET', 'POST'])]
    public function password(Request $request, User $user): Response
    {
        $em = $this->managerRegistry->getManager();
        $form = $this->createForm(UtilisateurPasswordType::class, $user)
            ->add('submit', SubmitType::class, ['label' => 'Valider']);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $this->userPasswordHasher->hashPassword($user, $form->getData()->getPlainPassword());
            $user->setPassword($password);
            $em->flush();

            $this->addFlash('success', 'Mot de passe changé');

            return $this->redirectToRoute('actravaux_utilisateur_show', ['id' => $user->getId()]);
        }

        return $this->render(
            '@AcMarcheTravaux/utilisateur/password.html.twig',
            [
                'user' => $user,
                'form' => $form->createView(),
            ]
        );
    }
}
