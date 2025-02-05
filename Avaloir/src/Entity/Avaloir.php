<?php

namespace AcMarche\Avaloir\Entity;

use AcMarche\Avaloir\Repository\AvaloirRepository;
use Stringable;
use DateTime;
use Exception;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: AvaloirRepository::class)]
#[ORM\Table(name: 'avaloir')]
class Avaloir implements TimestampableInterface, Stringable
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = 0;//todo pq ???
    #[ORM\Column(precision: 10, scale: 8, nullable: false)]
    public float $latitude = 0;
    #[ORM\Column(precision: 10, scale: 8, nullable: false)]
    public float $longitude = 0;
    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $description = null;
    #[ORM\Column(type: 'string', length: 120, nullable: true)]
    protected ?string $rue = null;
    #[ORM\Column(type: 'string', length: 120, nullable: true)]
    protected ?string $localite = null;
    #[ORM\Column(type: 'string', nullable: true)]
    protected ?string $numero = null;
    #[ORM\OneToMany(targetEntity: 'DateNettoyage', mappedBy: 'avaloir', cascade: ['persist', 'remove'])]
    private Collection $dates;
    #[ORM\OneToMany(targetEntity: 'Commentaire', mappedBy: 'avaloir', cascade: ['persist', 'remove'])]
    private Collection $commentaires;
    #[ORM\Column(type: 'date', nullable: true, options: ['comment' => 'date de rappel'])]
    #[Assert\Type(DateTime::class)]
    protected ?DateTimeInterface $date_rappel = null;
    #[Vich\UploadableField(mapping: 'avaloir_image', fileNameProperty: 'imageName')]
    private ?File $imageFile = null;
    #[ORM\Column(type: 'string', length: 120, nullable: true)]
    private ?string $imageName = null;
    #[ORM\Column(nullable: false)]
    public bool $finished = false;

    public function __construct()
    {
        $this->dates = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->rue." ".$this->numero;
    }

    /**
     * @throws Exception
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(string $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getRue(): ?string
    {
        return $this->rue;
    }

    public function setRue(?string $rue): self
    {
        $this->rue = $rue;

        return $this;
    }

    public function getLocalite(): ?string
    {
        return $this->localite;
    }

    public function setLocalite(?string $localite): self
    {
        $this->localite = $localite;

        return $this;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(?string $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getDateRappel(): ?DateTimeInterface
    {
        return $this->date_rappel;
    }

    public function setDateRappel(?DateTimeInterface $date_rappel): self
    {
        $this->date_rappel = $date_rappel;

        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): self
    {
        $this->imageName = $imageName;

        return $this;
    }

    /**
     * @return Collection|DateNettoyage[]
     */
    public function getDates(): Collection
    {
        return $this->dates;
    }

    public function addDate(DateNettoyage $date): self
    {
        if (!$this->dates->contains($date)) {
            $this->dates[] = $date;
            $date->setAvaloir($this);
        }

        return $this;
    }

    public function removeDate(DateNettoyage $date): self
    {
        if ($this->dates->contains($date)) {
            $this->dates->removeElement($date);
            // set the owning side to null (unless already changed)
            if ($date->getAvaloir() === $this) {
                $date->setAvaloir(null);
            }
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|Commentaire[]
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaire $commentaire): self
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires[] = $commentaire;
            $commentaire->setAvaloir($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): self
    {
        if ($this->commentaires->contains($commentaire)) {
            $this->commentaires->removeElement($commentaire);
            // set the owning side to null (unless already changed)
            if ($commentaire->getAvaloir() === $this) {
                $commentaire->setAvaloir(null);
            }
        }

        return $this;
    }
}
