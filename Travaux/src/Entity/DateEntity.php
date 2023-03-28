<?php

namespace AcMarche\Travaux\Entity;

use AcMarche\Travaux\Repository\DateRepository;
use Doctrine\ORM\Mapping as ORM;
use Stringable;

#[ORM\Entity(repositoryClass: DateRepository::class)]
#[ORM\Table()]
class DateEntity implements Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected int $id;

    #[ORM\Column(type: 'date', nullable: false)]
    public ?\DateTime $day;

    public function __construct(\DateTime $date = null)
    {
        $this->day = $date;
    }

    public function __toString(): string
    {
        return $this->day->format('Y-m-d');
    }


}