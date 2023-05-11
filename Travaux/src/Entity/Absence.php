<?php

namespace AcMarche\Travaux\Entity;

use AcMarche\Travaux\Repository\AbsenceRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AbsenceRepository::class)]
#[ORM\Table(name: 'absence')]
class Absence implements Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected int $id;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank]
    public string $raison;

    #[ORM\Column(type: 'date', nullable: false)]
    #[Assert\Type(type: DateTime::class)]
    #[Assert\GreaterThan(
        value: '1979-01-01',
    )]
    public ?DateTimeInterface $date_begin = null;

    #[Assert\Type(type: DateTime::class)]
    #[Assert\GreaterThan(
        value: '1979-01-01',
    )]
    #[Assert\GreaterThanOrEqual(propertyPath: 'date_begin')]
    #[ORM\Column(type: 'date', nullable: false)]
    public ?DateTimeInterface $date_end = null;

    #[ORM\ManyToOne(targetEntity: Employe::class, inversedBy: 'absences')]
    #[ORM\JoinColumn(nullable: false)]
    public Employe $employe;

    public function __construct(Employe $employe)
    {
        $this->employe = $employe;
    }

    public function __toString()
    {
        return 'absence x';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

}