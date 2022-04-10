<?php

namespace AcMarche\Avaloir\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;
use AcMarche\Avaloir\Entity\Avaloir;
use AcMarche\Avaloir\Entity\DateNettoyage;
use AcMarche\Avaloir\Entity\Quartier;
use AcMarche\Avaloir\Form\DateNettoyageType;
use AcMarche\Avaloir\Form\NettoyageQuartierType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * DateNettoyage controller.
 */
#[Route(path: '/datenettoyage')]
#[IsGranted('ROLE_TRAVAUX_AVALOIR')]
class DateNettoyageController extends AbstractController
{
    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }
    /**
     * Finds and displays a Date nettoyage entity.
     *
     *
     */
    #[Route(path: '/{id}', name: 'datenettoyage_show', methods: ['GET'])]
    public function show(DateNettoyage $date) : Response
    {
        return $this->render('@AcMarcheAvaloir/date_nettoyage/show.html.twig', array(
            'date' => $date,
        ));
    }
    /**
     * Displays a form to create a new DateNettoyage entity.
     *
     *
     */
    #[Route(path: '/new/{id}', name: 'datenettoyage_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Avaloir $avaloir) : Response
    {
        $dateNettoyage = new DateNettoyage();
        $dateNettoyage->setAvaloir($avaloir);
        $form = $this->createForm(
            DateNettoyageType::class,
            $dateNettoyage,
            [
                'action' => $this->generateUrl(
                    'datenettoyage_new',
                    ['id' => $avaloir->getId()]
                ),
            ]
        )
            ->add('Create', SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->managerRegistry->getManager();
            $em->persist($dateNettoyage);
            $em->flush();

            $this->addFlash('success', "La date a bien été ajoutée");

            return $this->redirectToRoute('avaloir_show', array('id' => $avaloir->getId()));
        }
        return $this->render('@AcMarcheAvaloir/date_nettoyage/new.html.twig', array(
            'entity' => $dateNettoyage,
            'form' => $form->createView(),
        ));
    }
    /**
     * Deletes a DateNettoyage entity.
     */
    #[Route(path: '/{id}', name: 'datenettoyage_delete', methods: ['POST'])]
    public function delete(Request $request, DateNettoyage $dateNettoyage) : RedirectResponse
    {
        $avaloir = $dateNettoyage->getAvaloir();
        if ($this->isCsrfTokenValid('delete'.$dateNettoyage->getId(), $request->request->get('_token'))) {

            $em = $this->managerRegistry->getManager();
            $em->remove($dateNettoyage);
            $em->flush();

            $this->addFlash('success', "La date a bien été supprimée");
        }
        return $this->redirectToRoute('avaloir_show', array('id' => $avaloir->getId()));
    }
    /**
     * Displays a form to create a new Suivis entity.
     *
     *
     */
    #[Route(path: '/new/quartier/{id}', name: 'nettoyage_quartier_new', methods: ['GET', 'POST'])]
    public function nettoyageQuartierNew(Request $request, Quartier $quartier) : Response
    {
        $entity = new DateNettoyage();
        $entity->setQuartier($quartier);
        $form = $this->createQuartierCreateForm($entity);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->managerRegistry->getManager();
            $rues = $quartier->getRues();
            $checkAvaloirs = false;

            foreach ($rues as $rue) {
                $avaloirs = $rue->getAvaloirs();
                foreach ($avaloirs as $avaloir) {
                    $checkAvaloirs = true;
                    $dateClone = clone($entity);
                    $dateClone->setAvaloir($avaloir);
                    $em->persist($dateClone);
                }
            }

            if (!$checkAvaloirs) {
                $this->addFlash("error", "Aucun avaloir associé à ce quartier !");
            } else {
                $em->flush();

                $this->addFlash('success', "La date a bien été ajoutée");
            }

            return $this->redirectToRoute('quartier_show', array('id' => $quartier->getId()));
        }
        return $this->render('@AcMarcheAvaloir/date_nettoyage/nettoyage_quartier_new.html.twig', array(
            'entity' => $entity,
            'quartier' => $quartier,
            'form' => $form->createView(),
        ));
    }
    private function createQuartierCreateForm(DateNettoyage $entity): FormInterface
    {
        $form = $this->createForm(
            NettoyageQuartierType::class,
            $entity);

        $form->add('submit', SubmitType::class, array('label' => 'Create'));

        return $form;
    }
}
