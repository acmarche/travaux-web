<?php

namespace AcMarche\Travaux\Entity;

use AcMarche\Travaux\Repository\DocumentRepository;
use Stringable;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
#[ORM\Table(name: 'document')]
class Document implements TimestampableInterface, Stringable
{
    use TimestampableTrait;
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected int $id;
    #[ORM\ManyToOne(targetEntity: Intervention::class, inversedBy: 'documents')]
    #[ORM\JoinColumn(nullable: false)]
    protected Intervention $intervention;

    #[Assert\File(maxSize: '7M')]
    protected File$Ofile;

    #[ORM\Column(type: 'string', length: 255, name: 'file_name')]
    protected string $fileName;

    #[ORM\Column(type: 'string')]
    protected string$mime;
    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => 0])]
    protected bool$smartphone = false;
    protected array$files;

    public function setOFile(File|UploadedFile $file = null): void
    {
        $this->Ofile = $file;

        if ($file !== null) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new DateTime('now');
        }
    }
    public function getOFile(): ?File
    {
        return $this->Ofile;
    }
    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }
    public function getFileName(): string
    {
        return $this->fileName;
    }
    public function __toString(): string
    {
        return $this->fileName;
    }
    /**
     * @return mixed
     */
    public function getFiles()
    {
        return $this->files;
    }
    /**
     * @param mixed $files
     */
    public function setFiles($files): void
    {
        $this->files = $files;
    }
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getMime(): string
    {
        return $this->mime;
    }
    public function setMime(string $mime): self
    {
        $this->mime = $mime;

        return $this;
    }
    public function getSmartphone(): bool
    {
        return $this->smartphone;
    }
    public function setSmartphone(bool $smartphone): self
    {
        $this->smartphone = $smartphone;

        return $this;
    }
    public function getIntervention(): ?Intervention
    {
        return $this->intervention;
    }
    public function setIntervention(?Intervention $intervention): self
    {
        $this->intervention = $intervention;

        return $this;
    }
    /**
     * STOP
     */
}
