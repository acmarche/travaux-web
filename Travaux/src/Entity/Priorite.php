<?php

namespace AcMarche\Travaux\Entity;

use AcMarche\Travaux\Repository\PrioriteRepository;
use Stringable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PrioriteRepository::class)]
#[ORM\Table(name: 'priorite')]
class Priorite implements Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected int $id;
    #[ORM\Column(type: 'string', nullable: false)]
    #[ORM\OrderBy(['intitule' => 'ASC'])]
    #[Assert\NotBlank]
    protected string $intitule;
    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private string $couleur;
    #[ORM\Column(type: 'string', length: 30, nullable: true)]
    private string $icone;
    #[ORM\OneToMany(targetEntity: Intervention::class, mappedBy: 'priorite')]
    private Collection $interventions;

    public function __construct()
    {
        $this->interventions = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string)$this->getIntitule();
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

    public function getCouleur(): string
    {
        return $this->couleur;
    }

    public function setCouleur(?string $couleur): self
    {
        $this->couleur = $couleur;

        return $this;
    }

    public function getIcone(): string
    {
        return $this->icone;
    }

    public function setIcone(?string $icone): self
    {
        $this->icone = $icone;

        return $this;
    }

    /**
     * @return Collection|Intervention[]
     */
    public function getInterventions(): Collection
    {
        return $this->interventions;
    }

    public function addIntervention(Intervention $intervention): self
    {
        if (!$this->interventions->contains($intervention)) {
            $this->interventions[] = $intervention;
            $intervention->setPriorite($this);
        }

        return $this;
    }

    public function removeIntervention(Intervention $intervention): self
    {
        if ($this->interventions->contains($intervention)) {
            $this->interventions->removeElement($intervention);
            // set the owning side to null (unless already changed)
            if ($intervention->getPriorite() === $this) {
                $intervention->setPriorite(null);
            }
        }

        return $this;
    }
    /**
     * STOP
     */
}
