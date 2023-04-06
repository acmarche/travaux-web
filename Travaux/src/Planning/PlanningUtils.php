<?php

namespace AcMarche\Travaux\Planning;

use AcMarche\Travaux\Entity\Employe;
use AcMarche\Travaux\Entity\InterventionPlanning;

class PlanningUtils
{
    /**
     * @param array|InterventionPlanning[] $interventions
     * @return array|Employe[]
     */
    public static function extractOuvriers(array $interventions): array
    {
        $ouvriers = [];
        foreach ($interventions as $intervention) {
            foreach ($intervention->employes as $employe) {
                $ouvriers[$employe->getId()] = $employe;
            }
        }

        return array_values($ouvriers);//reset index
    }

    public static function findIndex(Employe $employe, array $ouvriers): int
    {
        foreach ($ouvriers as $key => $ouvrier) {
            if ($ouvrier->getId() == $employe->getId()) {
                return $key;
            }
        }

        return 0;
    }
}