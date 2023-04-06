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

        return $ouvriers;
    }
}