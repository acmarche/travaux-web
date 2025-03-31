<?php
/**
 * Created by PhpStorm.
 * User: jfsenechal
 * Date: 19/09/16
 * Time: 15:09
 */

namespace AcMarche\Travaux\Service;

use AcMarche\Travaux\Entity\Document;
use AcMarche\Travaux\Entity\Intervention;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileHelper
{
    public function __construct(
        #[Autowire(param: 'ac_marche_travaux.upload.directory')]
        public string $path
    ) {
    }

    public function uploadFile(Intervention $intervention, UploadedFile $file, $fileName): File
    {
        $directory = $this->path.DIRECTORY_SEPARATOR.$intervention->getId();

        return $file->move($directory, $fileName);
    }

    public function deleteOneDoc(Document $document): bool
    {
        $intervention = $document->getIntervention();
        $id = $intervention->getId();
        if (!$id) {
            return false;
        }
        $file = $this->path.DIRECTORY_SEPARATOR.$id.DIRECTORY_SEPARATOR.$document->getFileName();

        $fs = new Filesystem();
        $fs->remove($file);

        return true;
    }

    public function deleteAllDocs(Intervention $intervention): bool
    {
        $id = $intervention->getId();
        $directory = $this->path.DIRECTORY_SEPARATOR.$id;
        if (!$id) {
            return false;
        }
        $fs = new Filesystem();
        $fs->remove($directory);

        return true;
    }

    /**
     * @param string $path
     * @return void
     */
    public function existOrCreateDirectory(string $path): void
    {

$filesystem = new Filesystem();
$filesystem->mkdir($path);

    }
}
