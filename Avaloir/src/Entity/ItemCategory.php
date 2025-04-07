<?php

namespace AcMarche\Avaloir\Entity;

use AcMarche\Avaloir\Repository\ItemCategoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Stringable;

#[ORM\Entity(repositoryClass: ItemCategoryRepository::class)]
#[ORM\Table(name: 'category_item')]
class ItemCategory implements Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    public ?int $id = 0;
    #[ORM\Column(length: 120, nullable: false)]
    public ?string $name = null;
    #[ORM\Column(length: 120, nullable: true)]
    public ?string $icon = null;

    public function __toString(): string
    {
        return $this->name;
    }

}
