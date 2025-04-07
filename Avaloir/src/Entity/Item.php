<?php

namespace AcMarche\Avaloir\Entity;

use AcMarche\Avaloir\Repository\ItemRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Stringable;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: ItemRepository::class)]
#[ORM\Table(name: 'item')]
class Item implements TimestampableInterface, Stringable
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    public ?int $id = 0;//todo pq ???
    #[ORM\Column(precision: 10, scale: 8, nullable: false)]
    public float $latitude = 0;
    #[ORM\Column(precision: 10, scale: 8, nullable: false)]
    public float $longitude = 0;
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $description = null;
    #[ORM\Column(type: 'string', length: 120, nullable: true)]
    public ?string $rue = null;
    #[ORM\Column(type: 'string', length: 120, nullable: true)]
    public ?string $localite = null;
    #[ORM\Column(type: 'string', nullable: true)]
    public ?string $numero = null;
    #[ORM\Column(type: 'date', nullable: true, options: ['comment' => 'date de rappel'])]
    #[Vich\UploadableField(mapping: 'item_image', fileNameProperty: 'imageName')]
    public ?File $imageFile = null;
    #[ORM\Column(type: 'string', length: 120, nullable: true)]
    public ?string $imageName = null;
    #[ORM\Column(nullable: false)]
    public bool $finished = false;
    #[ORM\ManyToOne(targetEntity: ItemCategory::class)]
    #[ORM\JoinColumn(nullable: true)]
    public ItemCategory $category;

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


}
