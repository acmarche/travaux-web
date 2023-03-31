<?php

namespace AcMarche\Travaux\Entity;

use AcMarche\Travaux\Repository\CategoryPlanningRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategoryPlanningRepository::class)]
#[ORM\Table(name: 'category_planning')]
class CategoryPlanning implements Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected int $id;
    #[ORM\Column(type: 'string', nullable: false)]
    #[ORM\OrderBy(['intitule' => 'ASC'])]
    #[Assert\NotBlank]
    public string $name;
    #[ORM\OneToMany(targetEntity: InterventionPlanning::class, mappedBy: 'category')]
    public Collection $interventions;
    #[ORM\OneToMany(targetEntity: Employe::class, mappedBy: 'category')]
    public Collection $employes;

    public function __construct()
    {
        $this->interventions = new ArrayCollection();
        $this->employes = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

}
