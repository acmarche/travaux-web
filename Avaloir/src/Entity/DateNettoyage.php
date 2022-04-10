<?php

namespace AcMarche\Avaloir\Entity;

use AcMarche\Avaloir\Repository\DateNettoyageRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Stringable;

#[ORM\Entity(repositoryClass: DateNettoyageRepository::class)]
#[ORM\Table(name: 'dates_nettoyage')]
class DateNettoyage implements TimestampableInterface, Stringable
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected int $id;

    #[ORM\Column(type: 'date', nullable: false)]
    protected DateTimeInterface $jour;
    #[ORM\ManyToOne(targetEntity: 'Avaloir', inversedBy: 'dates')]
    #[ORM\JoinColumn(nullable: true)]
    protected ?Avaloir $avaloir;
    /**
     * pour ajouter une date a chaque rue
     */
    protected ?Quartier $quartier = null;

    public function __toString(): string
    {
        return $this->jour->format('d-m-Y');
    }

    /**
     * @return mixed
     */
    public function getQuartier()
    {
        return $this->quartier;
    }

    /**
     * @param mixed $quartier
     */
    public function setQuartier($quartier): void
    {
        $this->quartier = $quartier;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getJour(): DateTimeInterface
    {
        return $this->jour;
    }

    public function setJour(DateTimeInterface $jour): self
    {
        $this->jour = $jour;

        return $this;
    }

    public function getAvaloir(): ?Avaloir
    {
        return $this->avaloir;
    }

    public function setAvaloir(?Avaloir $avaloir): self
    {
        $this->avaloir = $avaloir;

        return $this;
    }
}
