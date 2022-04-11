<?php
/**
 * Created by PhpStorm.
 * User: jfsenechal
 * Date: 8/12/16
 * Time: 10:26
 */

namespace AcMarche\Travaux\Event;

use DateTimeInterface;
use AcMarche\Travaux\Entity\Intervention;
use AcMarche\Travaux\Entity\Suivi;
use Symfony\Contracts\EventDispatcher\Event;

class InterventionEvent extends Event
{
    public const INTERVENTION_NEW = 'ac_marche_travaux.intervention.new';
    public const INTERVENTION_ACCEPT = 'ac_marche_travaux.intervention.accept';
    public const INTERVENTION_REJECT = 'ac_marche_travaux.intervention.reject';
    public const INTERVENTION_INFO = 'ac_marche_travaux.intervention.info';
    public const INTERVENTION_REPORTER = 'ac_marche_travaux.intervention.reporter';
    public const INTERVENTION_ARCHIVE = 'ac_marche_travaux.intervention.archive';
    public const INTERVENTION_SUIVI_NEW = 'ac_marche_travaux.intervention.suivi.new';

    public function __construct(
        protected Intervention $intervention,
        protected ?string $message,
        protected ?Suivi $suivi = null,
        protected ?DateTimeInterface $dateExecution = null
    ) {
    }

    public function getIntervention(): Intervention
    {
        return $this->intervention;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getSuivi(): ?Suivi
    {
        return $this->suivi;
    }

    public function getDateExecution(): ?DateTimeInterface
    {
        return $this->dateExecution;
    }

}
