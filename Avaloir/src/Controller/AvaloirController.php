<?php


namespace AcMarche\Avaloir\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AcMarche\Avaloir\Entity\Avaloir;
use AcMarche\Avaloir\Entity\DateNettoyage;
use AcMarche\Avaloir\Entity\Rue;
use AcMarche\Avaloir\Form\AvaloirEditType;
use AcMarche\Avaloir\Form\AvaloirType;
use AcMarche\Avaloir\Form\LocalisationType;
use AcMarche\Avaloir\Form\Search\SearchAvaloirType;
use AcMarche\Avaloir\Repository\AvaloirRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_TRAVAUX_AVALOIR')]
#[Route(path: '/avaloir')]
class AvaloirController extends AbstractController
{
    public function __construct(private AvaloirRepository $avaloirRepository, private ManagerRegistry $managerRegistry)
    {
    }

    #[Route(path: '/', name: 'avaloir', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $session = $request->getSession();
        $key = 'avaloir_search';
        $session->remove($key);
        $search_form = $this->createForm(
            SearchAvaloirType::class,
            [],
            array(
                'action' => $this->generateUrl('avaloir'),
                'method' => 'GET',
            )
        );
        $search_form->handleRequest($request);
        if ($search_form->isSubmitted() && $search_form->isValid()) {
            $data = $search_form->getData();
            $session->set($key, serialize($data));
            $avaloirs = $this->avaloirRepository->search($data);
        } else {
            $avaloirs = $this->avaloirRepository->findLast();
        }

        return $this->render(
            '@AcMarcheAvaloir/avaloir/index.html.twig',
            array(
                'search_form' => $search_form->createView(),
                'avaloirs' => $avaloirs,
                'search' => $search_form->isSubmitted(),
            )
        );
    }

    public function new(Request $request): RedirectResponse|Response
    {
        $avaloir = new Avaloir();
        $jour = new DateNettoyage();
        $avaloir->addDate($jour);

        $form = $this->createForm(AvaloirType::class, $avaloir)
            ->add('Create', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->managerRegistry->getManager();

            $data = $form->getData();
            $rueId = $data->getRueId();
            $dates = $data->getDates();

            if ($dates[0] instanceof DateNettoyage) {
                $jour2 = $dates[0]->getJour();
                if ($jour2 !== null) {
                    $jour->setAvaloir($avaloir);
                } else {
                    $avaloir->removeDate($jour);
                }
            }

            $rue = false;
            if ($rueId) {
                $rue = $em->getRepository(Rue::class)->find($rueId);
            }

            if (!$rue) {
                $this->addFlash("error", "La rue que vous avez choisi ne se trouve pas dans la liste des rues");

                return $this->redirectToRoute('avaloir_show');
            }

            $avaloir->setRue($rue);
            $em->persist($avaloir);
            $em->flush();
            $this->addFlash("success", "L'avaloir a bien été créé");

            return $this->redirectToRoute('avaloir_show', ['id' => $avaloir->getId()]);
        }

        return $this->render(
            '@AcMarcheAvaloir/avaloir/new.html.twig',
            array(
                'entity' => $avaloir,
                'form' => $form->createView(),
            )
        );
    }

    /**
     * Finds and displays a Avaloir entity.
     *
     *
     */
    #[Route(path: '/{id}', name: 'avaloir_show', methods: ['GET'])]
    public function show(Avaloir $avaloir): Response
    {
        $form = $this->createForm(
            LocalisationType::class,
            $avaloir,
            [
                'action' => $this->generateUrl('avaloir_localisation_update', ['id' => $avaloir->getId()]),
            ]
        );

        return $this->render(
            '@AcMarcheAvaloir/avaloir/show.html.twig',
            array(
                'avaloir' => $avaloir,
                'form' => $form->createView(),
            )
        );
    }

    /**
     * Displays a form to edit an existing Avaloir entity.
     *
     *
     */
    #[Route(path: '/{id}/edit', name: 'avaloir_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Avaloir $avaloir): Response
    {
        $em = $this->managerRegistry->getManager();
        $editForm = $this->createForm(AvaloirEditType::class, $avaloir)
            ->add('Update', SubmitType::class);
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em->flush();
            $this->addFlash("success", "L'avaloir a bien été modifié");

            return $this->redirectToRoute('avaloir_show', array('id' => $avaloir->getId()));
        }

        return $this->render(
            '@AcMarcheAvaloir/avaloir/edit.html.twig',
            array(
                'avaloir' => $avaloir,
                'edit_form' => $editForm->createView(),
            )
        );
    }

    /**
     * Deletes a Avaloir entity.
     */
    #[Route(path: '/{id}', name: 'avaloir_delete', methods: ['DELETE'])]
    public function delete(Request $request, Avaloir $avaloir): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$avaloir->getId(), $request->request->get('_token'))) {
            $em = $this->managerRegistry->getManager();
            $em->remove($avaloir);
            $em->flush();
            $this->addFlash("success", "L'avaloir a bien été supprimé");
        }

        return $this->redirectToRoute('avaloir');
    }
}
