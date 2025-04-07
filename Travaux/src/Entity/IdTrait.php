<?php

namespace AcMarche\Travaux\Entity;

use Doctrine\ORM\Mapping as ORM;
trait IdTrait
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    public ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}