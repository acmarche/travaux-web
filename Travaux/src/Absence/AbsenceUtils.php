<?php

namespace AcMarche\Travaux\Absence;

use AcMarche\Travaux\Entity\Employe;
use AcMarche\Travaux\Planning\DateProvider;

class AbsenceUtils
{
    public function __construct(private DateProvider $dateProvider)
    {

    }

    /**
     * @param Employe $employe
     * @return array<int, string>
     */
    public function getAllDaysAbsencesByEmploye(Employe $employe): array
    {
        $days = [];
        $format = 'Y-m-d';

        foreach ($employe->getAbsences() as $absence) {
            if ($absence->date_begin->format($format) != $absence->date_end->format($format)) {
                foreach ($this->dateProvider->daysBetween2Dates($absence->date_begin, $absence->date_end) as $date) {
                    $days[] = $date->format($format);
                }
            } else {
                $days [] = $absence->date_begin->format($format);
            }
        }

        return $days;
    }

    /**
     * @param Employe[] $employes
     * @return void
     */
    public function setVacationToEmployes(array $employes): void
    {
        foreach ($employes as $employe) {
            $employe->vacations = $this->getAllDaysAbsencesByEmploye($employe);
        }
    }
}