<?php

namespace AcMarche\Travaux\Entity;

use AcMarche\Travaux\Repository\HoraireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: HoraireRepository::class)]
#[ORM\Table(name: 'horaire')]
class Horaire implements Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected int $id;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank]
    public string $nom;

    #[ORM\OneToMany(mappedBy: 'horaire', targetEntity: Intervention::class)]
    public Collection $intervention;

    public function __toString()
    {
        return $this->nom;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __construct()
    {
        $this->intervention = new ArrayCollection();
    }
}