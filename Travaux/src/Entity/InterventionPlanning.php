<?php

namespace AcMarche\Travaux\Entity;

use AcMarche\Travaux\Repository\InterventionPlanningRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Stringable;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: InterventionPlanningRepository::class)]
#[ORM\Table(name: 'intervention_planning')]
class InterventionPlanning implements TimestampableInterface, Stringable
{
    use TimestampableTrait, DatesTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected int $id;

    #[ORM\Column(type: Types::TEXT, nullable: false)]
    public ?string $description;
    #[ORM\Column(type: 'string', nullable: false)]
    public string $user_add;
    #[ORM\ManyToOne(targetEntity: CategoryPlanning::class, inversedBy: 'interventions')]
    #[ORM\JoinColumn(nullable: false)]
    public ?CategoryPlanning $category = null;

    #[ORM\Column(type: 'string', nullable: false)]
    public ?string $lieu = null;

    #[ORM\ManyToOne(targetEntity: Horaire::class, inversedBy: 'intervention')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    public ?Horaire $horaire = null;

    #[ORM\ManyToMany(targetEntity: Employe::class)]
    public Collection $employes;

    public function __toString(): string
    {
        return $this->dates[0];
    }

    public function __construct()
    {
        $this->employes = new ArrayCollection();
        $this->datesCollection = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Employe>
     */
    public function getEmployes(): Collection
    {
        return $this->employes;
    }

    public function addEmploye(Employe $employe): self
    {
        if (!$this->employes->contains($employe)) {
            $this->employes->add($employe);
        }

        return $this;
    }

    public function removeEmploye(Employe $employe): self
    {
        $this->employes->removeElement($employe);

        return $this;
    }

}
