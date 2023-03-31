<?php

namespace AcMarche\Travaux\Planning;


use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use DateTimeInterface;

class DateProvider
{
    public function __construct(
        private CarbonFactory $carbonFactory
    ) {
    }
    /**
     * Names of days of the week.
     *
     * @return string[]
     */
    public function weekDaysName(): array
    {
        //todo dynamic first day of week
        //https://carbon.nesbot.com/docs/#api-week
        //$en->firstWeekDay); != $fr->firstWeekDay);

        /*  $days = [];
      /*  $translator = \Carbon\Translator::get(
              LocalHelper::getDefaultLocal()
          );

          foreach (Carbon::getDays() as $day) {
              $days[] = $translator->trans($day);
          }*/
        $days = Carbon::getDays();
        //if lundi first, on pousse dimanche a la fin
        $days[] = $days[0];
        unset($days[0]);

        return $days;
    }

    /**
     * @return int[]
     */
    public function getHours(): array
    {
        return range(1, CarbonInterface::HOURS_PER_DAY);
    }

    public function daysOfMonth(DateTimeInterface $date): CarbonPeriod
    {
        $carbon = $this->carbonFactory->instance($date);

        return Carbon::parse($carbon->firstOfMonth())->daysUntil(
            $carbon->endOfMonth()
        );
    }

    /**
     * Retourne la liste des semaines.
     *
     * @return CarbonPeriod[]
     */
    public function weeksOfMonth(DateTimeInterface $date): array
    {
        $weeks = [];
        $firstDayMonth = $this->carbonFactory->instance($date)->firstOfMonth();

        do {
            $weeks[] = $this->daysOfWeek($firstDayMonth); // point at the end of Week
            $firstDayMonth->nextWeekday(); //passe de dimanche a lundi
        } while ($firstDayMonth->isSameMonth($date));

        return $weeks;
    }

    public function daysOfWeek(CarbonInterface $date): CarbonPeriod
    {
        $firstDayOfWeek = $date->copy()->startOfWeek()->toMutable()->toDateString();
        $lastDayOffWeek = $date->endOfWeek()->toDateString(); //+6

        return Carbon::parse($firstDayOfWeek)->daysUntil($lastDayOffWeek)->locale(
            'fr'
        );
    }
}
