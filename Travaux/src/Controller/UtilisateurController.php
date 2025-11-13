<?php

namespace AcMarche\Travaux\Controller;

use AcMarche\Travaux\Entity\Security\User;
use AcMarche\Travaux\Form\Security\UtilisateurEditType;
use AcMarche\Travaux\Form\Security\UtilisateurType;
use AcMarche\Travaux\Repository\LdapRepository;
use AcMarche\Travaux\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/admin/utilisateur')]
#[IsGranted('ROLE_TRAVAUX_ADMIN')]
class UtilisateurController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $userPasswordHasher,
        private LdapRepository $ldapRepository
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

        $form = $this->createForm(UtilisateurType::class, $utilisateur);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $username = $form->get('username')->getData();
            if ($this->userRepository->findOneBy(['username' => $username])) {
                $this->addFlash("error", "L'utilisateur existe déjà");

                return $this->redirectToRoute('actravaux_utilisateur_new');
            }

            $userModel = $this->ldapRepository->getEntry($username);
            if ($userModel == null) {
                $this->addFlash("error", "L'utilisateur n'existe pas dans la LDAP");

                return $this->redirectToRoute('actravaux_utilisateur_new');
            }

            $user = User::createFromLdap($userModel);
            $randomPassword = bin2hex(random_bytes(16));
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $randomPassword));
            $this->userRepository->insert($user);

            $this->addFlash("success", "L'utilisateur a bien été ajouté");
            $this->addFlash("warning", "Attribué son rôle");

            return $this->redirectToRoute('actravaux_utilisateur_show', ['id' => $user->getId()]);
        }

        return $this->render(
            '@AcMarcheTravaux/utilisateur/new.html.twig',
            array(
                'utilisateur' => $utilisateur,
                'form' => $form,
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
            $this->userRepository->flush();
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

            $this->userRepository->remove($user);
            $this->userRepository->flush();
            $this->addFlash('success', 'L\'utilisateur a été supprimé');
        }

        return $this->redirectToRoute('actravaux_utilisateur');
    }

}
