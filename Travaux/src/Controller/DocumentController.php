<?php

namespace AcMarche\Travaux\Controller;

use AcMarche\Travaux\Document\DocumentHandler;
use AcMarche\Travaux\Entity\Document;
use AcMarche\Travaux\Entity\Intervention;
use AcMarche\Travaux\Form\DocumentType;
use AcMarche\Travaux\Repository\DocumentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/document')]
#[IsGranted('ROLE_TRAVAUX')]
class DocumentController extends AbstractController
{
    public function __construct(
        private DocumentRepository $documentRepository,
        private DocumentHandler $documentHandler
    ) {
    }

    #[Route(path: '/new/{id}', name: 'document_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_TRAVAUX_ADD')]
    public function new(Request $request, Intervention $intervention): Response
    {
        $document = new Document();
        $document->setIntervention($intervention);
        $form = $this->createForm(DocumentType::class, $document)
            ->add('Create', SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $files = $form->getData()->getFiles();

            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    try {
                        $this->documentHandler->handleFromUpload($intervention,$file);
                        $this->addFlash('success', 'Le document a bien été créé.');
                    } catch (FileException $error) {
                        $this->addFlash('danger', $error->getMessage());
                    }
                }
            }

            return $this->redirectToRoute('intervention_show', array('id' => $intervention->getId()));
        }

        return $this->render(
            '@AcMarcheTravaux/document/new.html.twig',
            array(
                'intervention' => $intervention,
                'form' => $form,
            )
        );
    }

    #[Route(path: '/{id}', name: 'document_show', methods: ['GET'])]
    public function show(Document $document): Response
    {
        return $this->render(
            '@AcMarcheTravaux/document/show.html.twig',
            array(
                'document' => $document,
            )
        );
    }

    #[Route(path: '/{id}', name: 'document_delete', methods: ['POST'])]
    #[IsGranted('ROLE_TRAVAUX_ADD')]
    public function delete(Request $request, Document $document): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$document->getId(), $request->request->get('_token'))) {

            $intervention = $document->getIntervention();

            try {
                $this->documentHandler->deleteOneDoc($document);
            } catch (IOException $exception) {
                $this->addFlash("danger", "Erreur de la suppression du document : ".$exception->getMessage());
            }

            $this->documentRepository->remove($document);
            $this->documentRepository->flush();

            $this->addFlash('success', 'Le document a bien été supprimé.');

            return $this->redirectToRoute('intervention_show', array('id' => $intervention->getId()));
        }

        return $this->redirectToRoute('intervention');
    }
}
