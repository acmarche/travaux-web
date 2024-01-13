<?php

namespace AcMarche\Travaux\Controller;

use AcMarche\Travaux\Entity\Intervention;
use AcMarche\Travaux\Event\InterventionEvent;
use AcMarche\Travaux\Form\ValidationType;
use AcMarche\Travaux\Service\InterventionWorkflow;
use AcMarche\Travaux\Service\TravauxUtils;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;


#[Route(path: '/validation')]
#[IsGranted('ROLE_TRAVAUX_VALIDATION')]
class ValidationController extends AbstractController
{
    private ValidatorInterface $validator;

    public function __construct(
        private TravauxUtils $travauxUtils,
        private EventDispatcherInterface $eventDispatcher,
        private InterventionWorkflow $interventionWorkflow,
        private ManagerRegistry $managerRegistry
    ) {
    }

    #[Route(path: '/', name: 'validation', methods: ['GET'])]
    public function index(): Response
    {
        $interventions = $this->travauxUtils->getInterventionsEnAttentes();

        return $this->render(
            '@AcMarcheTravaux/validation/index.html.twig',
            array('entities' => $interventions)
        );
    }

    #[Route(path: '/{id}', name: 'validation_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Intervention $intervention): Response
    {
        $result = [];
        $form = $this->createForm(ValidationType::class, $intervention);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->managerRegistry->getManager();

            $data = $form->getData();
            $dateExecution = null;

            $message = $form->get('message')->getData();

            if ($form->has('date_execution') && $data->getDateExecution()) {
                $dateExecution = $form->get('date_execution')->getData();
            }

            $event = new InterventionEvent($intervention, $message, null, $dateExecution);

            if ($form->get('accepter')->isClicked()) {
                $result = $this->interventionWorkflow->applyAccepter($intervention);
                if (isset($result['error'])) {
                    $this->addFlash("danger", $result['error']);
                } else {
                    $this->eventDispatcher->dispatch($event, InterventionEvent::INTERVENTION_ACCEPT);
                }
            }

            if ($form->get('refuser')->isClicked()) {
                $result = $this->interventionWorkflow->applyRefuser($intervention);
                if (isset($result['error'])) {
                    $this->addFlash("danger", $result['error']);
                } else {
                    $this->eventDispatcher->dispatch($event, InterventionEvent::INTERVENTION_REJECT);
                }

                //redirect to list because intervention deleted
                return $this->redirectToRoute('intervention');
            }

            if ($form->has('plusinfo') && $form->get('plusinfo')->isClicked()) {
                $result = $this->interventionWorkflow->applyPlusInfo($intervention);
                if (isset($result['error'])) {
                    $this->addFlash("danger", $result['error']);
                } else {
                    $this->eventDispatcher->dispatch($event, InterventionEvent::INTERVENTION_INFO);
                }
            }

            if ($form->has('reporter') && $form->get('reporter')->isClicked()) {
                if (!$dateExecution) {
                    $this->addFlash('danger', 'Veuillez indiquer une date d\'exécution');

                    return $this->redirectToRoute('validation_show', ['id' => $intervention->getId()]);
                }
                $result = $this->interventionWorkflow->applyAccepter($intervention);
                if (isset($result['error'])) {
                    $this->addFlash("danger", $result['error']);
                } else {
                    $this->eventDispatcher->dispatch($event, InterventionEvent::INTERVENTION_REPORTER);
                }
            }

            if (!isset($result['error'])) {
                $em->flush();
            }

            return $this->redirectToRoute('intervention_show', array('id' => $intervention->getId()));
        }

        return $this->render(
            '@AcMarcheTravaux/validation/show.html.twig',
            array(
                'entity' => $intervention,
                'form' => $form->createView(),
                'pdf' => false,
            )
        );
    }
}
