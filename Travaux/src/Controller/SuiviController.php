<?php

namespace AcMarche\Travaux\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use DateTime;
use AcMarche\Travaux\Entity\Intervention;
use AcMarche\Travaux\Entity\Suivi;
use AcMarche\Travaux\Event\InterventionEvent;
use AcMarche\Travaux\Form\SuiviType;
use AcMarche\Travaux\Service\SuiviService;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Suivi controller.
 */
#[Route(path: '/suivi')]
#[IsGranted('ROLE_TRAVAUX')]
class SuiviController extends AbstractController
{
    public function __construct(
        private SuiviService $suiviService,
        private EventDispatcherInterface $dispatcher,
        private ManagerRegistry $managerRegistry
    ) {
    }

    #[Route(path: '/new/{id}', name: 'suivi_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Intervention $intervention): Response
    {
        $suivi = $this->suiviService->initSuivi($intervention);
        $form = $this->createForm(SuiviType::class)
            ->add('Create', SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->suiviService->newSuivi($intervention, $form->getData()->getDescriptif());

            $event = new InterventionEvent($intervention, null, $suivi);
            $this->dispatcher->dispatch($event, InterventionEvent::INTERVENTION_SUIVI_NEW);

            $intervention = $suivi->getIntervention();

            $this->addFlash('success', 'Le suivi a bien été créé.');

            return $this->redirectToRoute('intervention_show', array('id' => $intervention->getId()));
        }

        return $this->render(
            '@AcMarcheTravaux/suivi/new.html.twig',
            array(
                'entity' => $suivi,
                'intervention' => $intervention,
                'form' => $form->createView(),
            )
        );
    }

    /**
     * Displays a form to edit an existing Suivi entity.
     *
     *
     */
    #[Route(path: '/{id}/edit', name: 'suivi_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Suivi $suivi): Response
    {
        $em = $this->managerRegistry->getManager();
        $editForm = $this->createForm(SuiviType::class, $suivi)
            ->add('Update', SubmitType::class);
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $user = $this->getUser();
            $intervention = $suivi->getIntervention();
            $userAdd = $suivi->getUserAdd();

            if ($userAdd == $user->getUserIdentifier()) {
                $intervention->setUpdatedAt(new DateTime());
                $em->persist($intervention);
                $em->flush();
                $this->addFlash('success', 'Le suivi a bien été mis à jour.');
            } else {
                $this->addFlash('warning', "Seul celui qui a ajouté le suivi peut le modifier");
            }

            return $this->redirectToRoute('intervention_show', array('id' => $intervention->getId()));
        }

        return $this->render(
            '@AcMarcheTravaux/suivi/edit.html.twig',
            array(
                'entity' => $suivi,
                'edit_form' => $editForm->createView(),
            )
        );
    }
}
