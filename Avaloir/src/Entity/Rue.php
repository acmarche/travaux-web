<?php

namespace AcMarche\Avaloir\Entity;

use AcMarche\Avaloir\Repository\RueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RueRepository::class)]
#[ORM\Table(name: 'rue')]
class Rue implements Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected int $id;

    #[ORM\Column(type: 'string', nullable: false)]
    #[ORM\OrderBy(['nom' => 'ASC'])]
    #[Assert\NotBlank]
    protected string $nom;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank]
    protected string $village;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected int $code;
    #[ORM\ManyToOne(targetEntity: Quartier::class, inversedBy: 'rues', cascade: ['remove'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private Quartier $quartier;

    private Collection|array $avaloirs = [];

    public function __construct()
    {
        $this->avaloirs = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->nom;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function setCode(?int $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getQuartier(): ?Quartier
    {
        return $this->quartier;
    }

    public function setQuartier(?Quartier $quartier): self
    {
        $this->quartier = $quartier;

        return $this;
    }

    public function getVillage(): string
    {
        return $this->village;
    }

    public function setVillage(string $village): self
    {
        $this->village = $village;

        return $this;
    }

    public function setAvaloirs(Collection $avaloirs): void
    {
        $this->avaloirs = $avaloirs;
    }

    public function getAvaloirs(): Collection
    {
        return $this->avaloirs;
    }
}
