<?php

namespace AcMarche\Travaux\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use DateTime;
use AcMarche\Travaux\Entity\Document;
use AcMarche\Travaux\Entity\Intervention;
use AcMarche\Travaux\Form\DocumentType;
use AcMarche\Travaux\Service\FileHelper;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Document controller.
 */
#[Route(path: '/document')]
#[IsGranted('ROLE_TRAVAUX')]
class DocumentController extends AbstractController
{
    public function __construct(private FileHelper $fileHelper, private ManagerRegistry $managerRegistry)
    {
    }
    /**
     * Displays a form to create a new Document entity.
     *
     *
     */
    #[Route(path: '/new/{id}', name: 'document_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_TRAVAUX_ADD')]
    public function new(Request $request, Intervention $intervention) : Response
    {
        $document = new Document();
        $document->setIntervention($intervention);
        $form = $this->createForm(DocumentType::class, $document)
            ->add('Create', SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->managerRegistry->getManager();
            $files = $form->getData()->getFiles();

            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $fileName = md5(uniqid('', true)).'.'.$file->guessClientExtension();

                    try {
                        $mime = $file->getMimeType();
                        $this->fileHelper->uploadFile(
                            $intervention,
                            $file,
                            $fileName
                        );
                        $document = new Document();
                        $document->setIntervention($intervention);
                        $document->setFileName($fileName);
                        $document->setMime($mime);
                        $document->setUpdatedAt(new DateTime('now'));
                        $em->persist($document);
                        $em->flush();
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
                'entity' => $document,
                'intervention' => $intervention,
                'form' => $form->createView(),
            )
        );
    }
    /**
     * Finds and displays a Document entity.
     *
     *
     */
    #[Route(path: '/{id}', name: 'document_show', methods: ['GET'])]
    public function show(Document $document) : Response
    {
        return $this->render(
            '@AcMarcheTravaux/document/show.html.twig',
            array(
                'entity' => $document,
            )
        );
    }
    /**
     * Deletes a Document entity.
     */
    #[Route(path: '/{id}', name: 'document_delete', methods: ['POST'])]
    #[IsGranted('ROLE_TRAVAUX_ADD')]
    public function delete(Request $request, Document $document) : RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$document->getId(), $request->request->get('_token'))) {

            $em = $this->managerRegistry->getManager();
            $intervention = $document->getIntervention();

            try {
                $this->fileHelper->deleteOneDoc($document);
            } catch (IOException $exception) {
                $this->addFlash("danger", "Erreur de la suppression du document : ".$exception->getMessage());
            }

            $em->remove($document);
            $em->flush();

            $this->addFlash('success', 'Le document a bien été supprimé.');

            return $this->redirectToRoute('intervention_show', array('id' => $intervention->getId()));
        }
        return $this->redirectToRoute('intervention');
    }
}
