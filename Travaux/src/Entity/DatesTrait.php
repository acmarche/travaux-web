<?php

namespace AcMarche\Travaux\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

trait DatesTrait
{
    #[ORM\Column(type: 'json', nullable: false)]
    public array $dates = [];

    public Collection $datesCollection;

    /**
     * @return Collection<int, DateTimeInterface>|array<DateTimeInterface>
     */
    public function getDates(): Collection|array
    {
        return $this->datesCollection;
    }

    public function addDate(DateTimeInterface $date): self
    {
        if (!$this->datesCollection->contains($date)) {
            $this->datesCollection->add($date);
        }

        return $this;
    }

    public function removeDate(DateTimeInterface $date): self
    {
        $this->datesCollection->removeElement($date);

        return $this;
    }
}