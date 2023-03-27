<?php

namespace AcMarche\Travaux\Entity;

use AcMarche\Travaux\Repository\EmployeRepository;
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

    public function __toString()
    {
        return $this->nom.' '.$this->prenom;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}