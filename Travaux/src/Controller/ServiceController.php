<?php

namespace AcMarche\Travaux\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use AcMarche\Travaux\Entity\Service;
use AcMarche\Travaux\Form\ServiceType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Service controller.
 */
#[Route(path: '/service')]
#[IsGranted('ROLE_TRAVAUX_ADMIN')]
class ServiceController extends AbstractController
{
    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }
    /**
     * Lists all Service entities.
     *
     *
     */
    #[Route(path: '/', name: 'service', methods: ['GET'])]
    public function index() : Response
    {
        $em = $this->managerRegistry->getManager();
        $entities = $em->getRepository(Service::class)->findAll();
        return $this->render(
            '@AcMarcheTravaux/service/index.html.twig',
            array(
                'entities' => $entities,
            )
        );
    }
    /**
     * Displays a form to create a new Service entity.
     *
     *
     */
    #[Route(path: '/new', name: 'service_new', methods: ['GET', 'POST'])]
    public function new(Request $request) : Response
    {
        $service = new Service();
        $form = $this->createForm(ServiceType::class, $service)
            ->add('Create', SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->managerRegistry->getManager();

            $em->persist($service);
            $em->flush();

            $this->addFlash('success', 'Le service a bien été créé.');

            return $this->redirectToRoute('service_show', array('id' => $service->getId()));
        }
        return $this->render(
            '@AcMarcheTravaux/service/new.html.twig',
            array(
                'entity' => $service,
                'form' => $form->createView(),
            )
        );
    }
    /**
     * Finds and displays a Service entity.
     *
     *
     */
    #[Route(path: '/{id}', name: 'service_show', methods: ['GET'])]
    public function show(Service $service) : Response
    {
        return $this->render(
            '@AcMarcheTravaux/service/show.html.twig',
            array(
                'entity' => $service,
            )
        );
    }
    /**
     * Displays a form to edit an existing Service entity.
     *
     *
     */
    #[Route(path: '/{id}/edit', name: 'service_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Service $service) : Response
    {
        $editForm = $this->createForm(ServiceType::class, $service)
            ->add('Update', SubmitType::class);
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->managerRegistry->getManager();
            $em->flush();

            $this->addFlash('success', 'Le service a bien été modifié.');

            return $this->redirectToRoute('service_show', array('id' => $service->getId()));
        }
        return $this->render(
            '@AcMarcheTravaux/service/edit.html.twig',
            array(
                'entity' => $service,
                'edit_form' => $editForm->createView(),
            )
        );
    }
    /**
     * Deletes a Service entity.
     */
    #[Route(path: '/{id}', name: 'service_delete', methods: ['POST'])]
    public function delete(Request $request, Service $service) : RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$service->getId(), $request->request->get('_token'))) {

            $em = $this->managerRegistry->getManager();

            $em->remove($service);
            $em->flush();

            $this->addFlash('success', 'Le service a bien été supprimé.');
        }
        return $this->redirectToRoute('service');
    }
}
