<?php

namespace Grr\Core\Provider;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Grr\Core\Contrat\Entity\AreaInterface;
use Grr\Core\Contrat\Entity\EntryInterface;
use Grr\Core\Factory\CarbonFactory;
use Grr\Core\Model\TimeSlot;

class TimeSlotsProvider
{
    public function __construct(
        private CarbonFactory $carbonFactory
    ) {
    }

    /**
     * Crée les tranches d'heures sous forme d'objet.
     *
     * @return TimeSlot[]
     */
    public function getTimeSlotsModelByAreaAndDaySelected(AreaInterface $area, CarbonInterface $dateSelected): array
    {
        $startTime = $area->getStartTime();
        $endTime = $area->getEndTime();
        $timeInterval = $area->getTimeInterval();

        $carbonPeriod = $this->getTimeSlots($dateSelected, $startTime, $endTime, $timeInterval);

        $timeSlots = [];
        $carbonPeriod->rewind();
        $last = $carbonPeriod->last();
        $carbonPeriod->rewind();

        while ($carbonPeriod->current()->lessThan($last)) {
            $begin = $carbonPeriod->current();
            $carbonPeriod->next();
            $end = $carbonPeriod->current();

            $timeSlots[] = new TimeSlot($begin, $end);
        }

        return $timeSlots;
    }

    /**
     * Retourne les tranches d'heures d'après une heure de début, de fin et d'un interval de temps.
     */
    public function getTimeSlots(
        CarbonInterface $dateSelected,
        int $hourBegin,
        int $hourEnd,
        int $timeInterval
    ): CarbonPeriod {
        $dateBegin = $dateSelected->copy();
        $dateEnd = $dateSelected->copy();

        $dateBegin->hour($hourBegin);
        $dateEnd->hour($hourEnd);

        return Carbon::parse($dateBegin)->minutesUntil($dateEnd, $timeInterval);
    }

    /**
     * Obtient les tranches horaires de l'entrée basée sur la résolution de l'Area.
     */
    public function getTimeSlotsByEntry(EntryInterface $entry): CarbonPeriod
    {
        $area = $entry->getRoom()->getArea();
        $entryHourBegin = $entry->getStartTime();
        $entryHourEnd = $entry->getEndTime();

        return Carbon::parse($entryHourBegin)->minutesUntil($entryHourEnd, $area->getTimeInterval());
    }
}
