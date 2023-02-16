<?php

namespace AcMarche\Avaloir\Controller;

use AcMarche\Avaloir\Entity\Quartier;
use AcMarche\Avaloir\Entity\Rue;
use AcMarche\Avaloir\Form\QuartierRueType;
use AcMarche\Avaloir\Service\TokenService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route(path: '/quartier/rue')]
#[IsGranted('ROLE_TRAVAUX_AVALOIR')]
class QuartierRueController extends AbstractController
{
    public function __construct(private ManagerRegistry $managerRegistry, private TokenService $tokenService)
    {
    }

    #[Route(path: '/new/{id}', name: 'quartierrue_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Quartier $quartier): Response
    {
        $em = $this->managerRegistry->getManager();
        $ruesTmp = $em->getRepository(Rue::class)->getByQuartier($quartier);
        $ruesOld = array();
        foreach ($ruesTmp as $rue) {
            $ruesOld[] = $rue->getId();
        }
        $form = $this->createCreateForm($quartier);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $rueIds = $request->request->all('itemsdp');
            $rues = $rueIds ? array_unique($rueIds) : array();
            //donnees en int sinon comparaison faussee
            foreach ($rues as $key => $rue) {
                $rues[$key] = (int)$rue;
            }

            $diff = count(array_diff($ruesOld, $rues));
            $diff_count = count($ruesOld) - count($rues);

            if ($diff === 0 && $diff_count === 0) {
                $this->addFlash("warning", "Aucun changement n'a été effectué");

                return $this->redirectToRoute('quartier_show', array('id' => $quartier->getId()));
            }

            $enMoins = array_diff($ruesOld, $rues);
            $enPlus = array_diff($rues, $ruesOld);

            $this->setRues($quartier, $enPlus, 'add');
            $this->setRues($quartier, $enMoins, 'remove');

            $em->flush();

            $this->addFlash('success', 'Les rues ont bien modifiées.');

            return $this->redirectToRoute('quartier_show', array('id' => $quartier->getId()));
        }
        /**
         * Rues deja associees
         */
        $rues = $this->tokenService->destinatairesToArray($quartier);

        return $this->render('@AcMarcheAvaloir/quartier_rue/new.html.twig', array(
            'entity' => $quartier,
            'rues' => $rues,
            'form' => $form->createView(),
        ));
    }

    /**
     * Associe ou desassocie une rue
     * @param Quartier $quartier
     * @param array $rues
     * @param string $action
     */
    protected function setRues($quartier, $rues, $action): void
    {
        $em = $this->managerRegistry->getManager();

        foreach ($rues as $rueId) {
            $rueId = (int)$rueId;
            if ($rueId !== 0) {
                $args = array('id' => $rueId);
                $rue = $em->getRepository(Rue::class)->findOneBy($args);
                if ($rue) {
                    if ($action === 'add') {
                        $rue->setQuartier($quartier);
                    } elseif ($action === 'remove') {
                        $rue->setQuartier(null);
                    }
                    $em->persist($rue);
                }
            }
        }
    }

    private function createCreateForm(Quartier $entity): FormInterface
    {
        $form = $this->createForm(QuartierRueType::class, $entity, array(
            'action' => $this->generateUrl('quartierrue_new', array('id' => $entity->getId())),
            'method' => 'POST',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Update'));

        return $form;
    }
}
