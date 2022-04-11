<?php
/**
 * Created by PhpStorm.
 * User: jfsenechal
 * Date: 19/09/16
 * Time: 15:09
 */

namespace AcMarche\Travaux\Service;

use Symfony\Component\HttpFoundation\File\File;
use AcMarche\Travaux\Entity\Document;
use AcMarche\Travaux\Entity\Intervention;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileHelper
{
    private string $path;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->path = $parameterBag->get('ac_marche_travaux.upload.directory');
    }

    public function uploadFile(Intervention $intervention, UploadedFile $file, $fileName): File
    {
        $directory = $this->path . DIRECTORY_SEPARATOR . $intervention->getId();

        return $file->move($directory, $fileName);
    }

    public function deleteOneDoc(Document $document): bool
    {
        $intervention = $document->getIntervention();
        $id = $intervention->getId();
        if (!$id) {
            return false;
        }
        $file = $this->path . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . $document->getFileName();

        $fs = new Filesystem();
        $fs->remove($file);
        return true;
    }

    public function deleteAllDocs(Intervention $intervention): bool
    {
        $id = $intervention->getId();
        $directory = $this->path . DIRECTORY_SEPARATOR . $id;
        if (!$id) {
            return false;
        }
        $fs = new Filesystem();
        $fs->remove($directory);

        return true;
    }
}
