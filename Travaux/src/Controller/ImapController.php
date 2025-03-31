<?php

namespace AcMarche\Travaux\Controller;

use AcMarche\Travaux\Document\DocumentHandler;
use AcMarche\Travaux\Entity\Intervention;
use AcMarche\Travaux\Event\InterventionEvent;
use AcMarche\Travaux\Form\ImapLoginType;
use AcMarche\Travaux\Form\InterventionFromMessageType;
use AcMarche\Travaux\Imap\CryptoHelper;
use AcMarche\Travaux\Imap\ImapConnectTrait;
use AcMarche\Travaux\Imap\ImapHandler;
use AcMarche\Travaux\Repository\CategorieRepository;
use AcMarche\Travaux\Repository\EtatRepository;
use AcMarche\Travaux\Repository\InterventionRepository;
use AcMarche\Travaux\Repository\PrioriteRepository;
use AcMarche\Travaux\Service\InterventionWorkflow;
use DirectoryTree\ImapEngine\Attachment;
use DirectoryTree\ImapEngine\Collections\MessageCollection;
use DirectoryTree\ImapEngine\Exceptions\ImapConnectionFailedException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route(path: '/imap')]
class ImapController extends AbstractController
{
    use ImapConnectTrait;

    public function __construct(
        private readonly InterventionRepository $interventionRepository,
        private readonly EtatRepository $etatRepository,
        private readonly PrioriteRepository $prioriteRepository,
        private readonly CategorieRepository $categorieRepository,
        private readonly InterventionWorkflow $workflow,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ImapHandler $imapHandler,
        private readonly CryptoHelper $cryptoHelper,
        private readonly DocumentHandler $documentHandler
    ) {
    }

    #[Route(path: '/login', name: 'imap_login')]
    #[IsGranted('ROLE_TRAVAUX_ADD')]
    public function login(Request $request): Response
    {
        $request->getSession()->set('imap_password', null);
        $user = $this->getUser();
        $data = ['username' => $user->getUsername()];
        $form = $this->createForm(ImapLoginType::class, $data);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('password')->getData();
            $passwordCrypted = $this->cryptoHelper->encrypt($password);
            try {
                $this->tryConnectImap($user->getUsername(), $password);
            } catch (ImapConnectionFailedException|\Exception $e) {
                $this->addFlash('danger', $e->getMessage());

                return $this->redirectToRoute('imap_login');
            }
            if ($this->imapHandler->isConnected()) {
                $request->getSession()->set('imap_password', $passwordCrypted);

                return $this->redirectToRoute('imap_index');
            }

            return $this->redirectToRoute('imap_login');
        }

        return $this->render('@AcMarcheTravaux/imap/login.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route(path: '/', name: 'imap_index')]
    #[IsGranted('ROLE_TRAVAUX_ADD')]
    public function index(Request $request): Response
    {
        try {
            $this->connectImap($request);
        } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());

            return $this->redirectToRoute('imap_login');
        }

        try {
            $messages = $this->imapHandler->messages();
        } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
            $messages = new MessageCollection();
        }

        $user = $this->getUser();

        return $this->render('@AcMarcheTravaux/imap/index.html.twig', [
            'messages' => $messages,
            'user' => $user,
        ]);
    }

    #[Route(path: '/show/{uid}', name: 'imap_show')]
    #[IsGranted('ROLE_TRAVAUX_ADD')]
    public function show(Request $request, string $uid): Response
    {
        try {
            $this->connectImap($request);
        } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());

            return $this->redirectToRoute('imap_login');
        }

        if (!$message = $this->imapHandler->message($uid)) {
            $this->addFlash('danger', 'Message not found');

            return $this->redirectToRoute('imap_index');
        }

        $intervention = Intervention::newFromMessage($message);

        $form = $this->createForm(InterventionFromMessageType::class, $intervention, [
            'attachments' => $intervention->attachments,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $intervention->setUserAdd($user->getUserIdentifier());
            $intervention->etat = $this->etatRepository->find(1);
            $intervention->priorite = $this->prioriteRepository->find(1);
            $intervention->categorie = $this->categorieRepository->find(3);

            $this->interventionRepository->persist($intervention);
            $this->interventionRepository->flush();

            $data = $form->getData();
            $attachments = $data->attachments;
            foreach ($attachments as $attachmentSelected) {
                $filteredAttachments = array_filter(
                    $message->attachments(),
                    function (Attachment $attachment) use ($attachmentSelected) {
                        return $attachment->contentId() == $attachmentSelected;
                    }
                );
                if (count($filteredAttachments) > 0) {
                    $attachment = reset($filteredAttachments) ?: null;
                    try {
                        $this->documentHandler->handleFromImap($intervention, $attachment);
                    } catch (\Exception $e) {
                        $this->addFlash('danger', $e->getMessage());
                    }
                } else {
                    $this->addFlash('warning', 'Attachment not found');
                }
            }

            $this->workflow->newIntervention($intervention);

            $this->interventionRepository->flush();
            $this->addFlash('success', 'L\'intervention a bien été crée.');

            $event = new InterventionEvent($intervention, null);
            $this->eventDispatcher->dispatch($event, InterventionEvent::INTERVENTION_NEW);

            return $this->redirectToRoute('intervention_edit', ['id' => $intervention->getId()]);
        }

        return $this->render('@AcMarcheTravaux/imap/show.html.twig', [
            'message' => $message,
            'intervention' => $intervention,
            'form' => $form,
        ]);
    }

}
