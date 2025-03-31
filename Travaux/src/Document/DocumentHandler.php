<?php

namespace AcMarche\Travaux\Document;

use AcMarche\Travaux\Entity\Document;
use AcMarche\Travaux\Entity\Intervention;
use AcMarche\Travaux\Repository\DocumentRepository;
use AcMarche\Travaux\Service\FileHelper;
use DirectoryTree\ImapEngine\Attachment;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DocumentHandler
{
    public function __construct(
        private readonly DocumentRepository $documentRepository,
        private readonly FileHelper $fileHelper,
    ) {
    }

    /**
     * @param Intervention $intervention
     * @param UploadedFile $file
     * @return void
     */
    public function handleFromUpload(Intervention $intervention, UploadedFile $file): void
    {
        $fileName = md5(uniqid('', true)).'.'.$file->guessClientExtension();

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
        $document->setUpdatedAt(new \DateTime('now'));
        $this->documentRepository->persist($document);
        $this->documentRepository->flush();
    }

    public function handleFromImap(Intervention $intervention, Attachment $attachment): void
    {
        $fileName = md5(uniqid('', true)).'.'.$attachment->extension();
        $directory = $this->fileHelper->path.DIRECTORY_SEPARATOR.$intervention->getId().DIRECTORY_SEPARATOR;

        $this->fileHelper->existOrCreateDirectory($directory);

        $attachment->save(
            $directory.$fileName
        );

        $document = new Document();
        $document->setIntervention($intervention);
        $document->setFileName($fileName);
        $document->setMime($attachment->contentType());
        $document->setUpdatedAt(new \DateTime('now'));
        $this->documentRepository->persist($document);
        $this->documentRepository->flush();
    }

    public function deleteOneDoc(Document $document): void
    {
        $this->fileHelper->deleteOneDoc($document);
    }
}