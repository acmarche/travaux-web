<?php

namespace AcMarche\Travaux\Entity;

use AcMarche\Travaux\Repository\CategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategorieRepository::class)]
#[ORM\Table(name: 'categorie')]
class Categorie implements Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected int $id;
    #[ORM\Column(type: 'string', nullable: false)]
    #[ORM\OrderBy(['intitule' => 'ASC'])]
    #[Assert\NotBlank]
    protected string $intitule;
    #[ORM\OneToMany(targetEntity: Intervention::class, mappedBy: 'categorie')]
    private Collection $intervention;

    public function __construct()
    {
        $this->intervention = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string)$this->intitule;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIntitule(): ?string
    {
        return $this->intitule;
    }

    public function setIntitule(string $intitule): self
    {
        $this->intitule = $intitule;

        return $this;
    }

    /**
     * @return Collection|Intervention[]
     */
    public function getIntervention(): Collection
    {
        return $this->intervention;
    }

    public function addIntervention(Intervention $intervention): self
    {
        if (!$this->intervention->contains($intervention)) {
            $this->intervention[] = $intervention;
            $intervention->setCategorie($this);
        }

        return $this;
    }

    public function removeIntervention(Intervention $intervention): self
    {
        if ($this->intervention->contains($intervention)) {
            $this->intervention->removeElement($intervention);
            // set the owning side to null (unless already changed)
            if ($intervention->getCategorie() === $this) {
                $intervention->setCategorie(null);
            }
        }

        return $this;
    }
    /**
     * STOP
     */
}
