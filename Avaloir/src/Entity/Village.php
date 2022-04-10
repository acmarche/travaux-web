<?php

namespace AcMarche\Avaloir\Entity;

use AcMarche\Avaloir\Repository\VillageRepository;
use Stringable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VillageRepository::class)]
#[ORM\Table(name: 'village')]
class Village implements Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected int $id;
    #[ORM\Column(type: 'string', nullable: false)]
    #[ORM\OrderBy(['nom' => 'ASC'])]
    #[Assert\NotBlank]
    protected string $nom;
    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $couleur;
    #[ORM\Column(type: 'string', length: 30, nullable: true)]
    private ?string $icone;

    public function __construct()
    {
    }

    public function __toString(): string
    {
        return (string)$this->getNom();
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
}
