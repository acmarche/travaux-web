<?php

namespace AcMarche\Travaux\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

trait UuidTrait
{
    #[ORM\Column(type: 'uuid', unique: true, nullable: false)]
    public ?string $uuid = null;

    public function generateUuid(): string
    {
        return Uuid::v4();
    }
}
