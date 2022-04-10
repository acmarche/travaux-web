<?php

namespace AcMarche\Avaloir\Entity;

use AcMarche\Avaloir\Repository\QuartierRepository;
use Stringable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: QuartierRepository::class)]
#[ORM\Table(name: 'quartier')]
class Quartier implements Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected int $id;
    #[ORM\Column(type: 'string', nullable: false)]
    #[ORM\OrderBy(['nom' => 'ASC'])]
    #[Assert\NotBlank]
    protected string $nom;
    #[ORM\OneToMany(targetEntity: 'Rue', mappedBy: 'quartier')]
    private Collection $rues;

    private Collection $rueids;

    public function __toString(): string
    {
        return (string)$this->nom;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->rues = new ArrayCollection();
        $this->rueids = new ArrayCollection();
    }

    public function setRueIds($rues): static
    {
        $this->rueids = $rues;

        return $this;
    }

    public function getRueIds(): Collection
    {
        return $this->rueids;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection|Rue[]
     */
    public function getRues(): Collection
    {
        return $this->rues;
    }

    public function addRue(Rue $rue): self
    {
        if (!$this->rues->contains($rue)) {
            $this->rues[] = $rue;
            $rue->setQuartier($this);
        }

        return $this;
    }

    public function removeRue(Rue $rue): self
    {
        if ($this->rues->contains($rue)) {
            $this->rues->removeElement($rue);
            // set the owning side to null (unless already changed)
            if ($rue->getQuartier() === $this) {
                $rue->setQuartier(null);
            }
        }

        return $this;
    }
    /**
     * STOP
     */
}
