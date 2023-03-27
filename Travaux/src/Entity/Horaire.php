<?php

namespace AcMarche\Travaux\Entity;

use AcMarche\Travaux\Repository\HoraireRepository;
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

    public function __toString()
    {
        return $this->nom;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}