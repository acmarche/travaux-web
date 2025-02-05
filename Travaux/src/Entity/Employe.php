<?php

namespace AcMarche\Travaux\Entity;

use AcMarche\Travaux\Repository\EmployeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EmployeRepository::class)]
#[ORM\Table(name: 'employe')]
class Employe implements Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected int $id;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank]
    public string $nom;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank]
    public string $prenom;

    #[ORM\ManyToMany(targetEntity: CategoryPlanning::class)]
    public Collection|null $categories;

    #[ORM\OneToMany(targetEntity: Absence::class, mappedBy: 'employe', cascade: ['remove'])]
    public Collection|null $absences;

    /**
     * @var array<int, string> $vacations
     */
    public array $vacations = [];
    public ?string $reason_absence = null;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->absences = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->nom.' '.$this->prenom;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function inVacation(\DateTimeInterface $date): bool
    {
        return in_array($date->format('Y-m-d'), $this->vacations);
    }

    public function getNomPrenom(): string
    {
        return $this->nom.' '.$this->prenom;
    }

    /**
     * @return Collection<int, CategoryPlanning>|array<CategoryPlanning>
     */
    public function getCategories(): Collection|array
    {
        return $this->categories;
    }

    public function addCategory(CategoryPlanning $categoryPlanning): self
    {
        if (!$this->categories->contains($categoryPlanning)) {
            $this->categories->add($categoryPlanning);
        }

        return $this;
    }

    public function removeCategory(CategoryPlanning $categoryPlanning): self
    {
        $this->categories->removeElement($categoryPlanning);

        return $this;
    }

    /**
     * @return Collection<int, Absence>|array<Absence>
     */
    public function getAbsences(): Collection|array
    {
        return $this->absences;
    }

    public function addAbsence(Absence $absence): self
    {
        if (!$this->absences->contains($absence)) {
            $this->absences->add($absence);
        }

        return $this;
    }

    public function removeAbsence(Absence $absence): self
    {
        $this->absences->removeElement($absence);

        return $this;
    }
}