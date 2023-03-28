<?php

namespace AcMarche\Travaux\Planning;

use AcMarche\Travaux\Entity\Intervention;
use Doctrine\Common\Collections\ArrayCollection;

class TreatmentDates
{
    public static function setDatesFromCollection(Intervention $intervention, Intervention $data)
    {
        $dates = [];
        foreach ($data->datesCollection as $date) {
            if ($date instanceof \DateTimeInterface) {
                $dates [] = $date->format('Y-m-d');
            }
        }
        $intervention->dates = $dates;
    }

    public static function setDatesCollectionFromDates(Intervention $intervention)
    {
        $intervention->datesCollection = new ArrayCollection();
        foreach ($intervention->dates as $date) {
            $intervention->datesCollection->add(\DateTime::createFromFormat('Y-m-d', $date));
        }
    }
}