<?php

namespace AcMarche\Travaux\Entity;

use AcMarche\Travaux\Repository\SuiviRepository;
use Stringable;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SuiviRepository::class)]
#[ORM\Table(name: 'suivis')]
class Suivi implements TimestampableInterface, Stringable
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected int $id;

    #[ORM\Column(type: 'text', nullable: false)]
    protected string $descriptif;
    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => 0])]
    protected bool $smartphone = false;
    #[ORM\ManyToOne(targetEntity: Intervention::class, inversedBy: 'suivis')]
    #[ORM\JoinColumn(nullable: false)]
    protected Intervention $intervention;
    #[ORM\Column(type: 'string', nullable: false)]
    protected string $user_add = "";

    public function __toString(): string
    {
        return $this->descriptif;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescriptif(): string
    {
        return $this->descriptif;
    }

    public function setDescriptif(string $descriptif): self
    {
        $this->descriptif = $descriptif;

        return $this;
    }

    public function getSmartphone(): bool
    {
        return $this->smartphone;
    }

    public function setSmartphone(bool $smartphone): self
    {
        $this->smartphone = $smartphone;

        return $this;
    }

    public function getUserAdd(): ?string
    {
        return $this->user_add;
    }

    public function setUserAdd(string $user_add): self
    {
        $this->user_add = $user_add;

        return $this;
    }

    public function getIntervention(): ?Intervention
    {
        return $this->intervention;
    }

    public function setIntervention(?Intervention $intervention): self
    {
        $this->intervention = $intervention;

        return $this;
    }
}
