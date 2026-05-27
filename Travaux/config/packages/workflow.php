<?php

use AcMarche\Travaux\Entity\Intervention;
use AcMarche\Travaux\Service\WorkflowEnum;
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework) {
    $interventionPublishing = $framework->workflows()->workflow('intervention_publication');
    $interventionPublishing
        ->type('state_machine')
        ->supports([Intervention::class])
        ->initialMarking([WorkflowEnum::AUTEUR_CHECKING->value]);

    $interventionPublishing->auditTrail()->enabled(true);
    $interventionPublishing->markingStore()
        ->type('method')
        ->property('currentPlace');

    $interventionPublishing->place(WorkflowEnum::REDACTEUR->value);
    $interventionPublishing->place(WorkflowEnum::AUTEUR_CHECKING->value);
    $interventionPublishing->place(WorkflowEnum::ADMIN_CHECKING->value);
    $interventionPublishing->place(WorkflowEnum::DELETED->value);
    $interventionPublishing->place(WorkflowEnum::PUBLISHED->value);

    $interventionPublishing->transition()
        ->name('auteur_accept')
        ->from(WorkflowEnum::AUTEUR_CHECKING->value)
        ->to(WorkflowEnum::ADMIN_CHECKING->value);

    $interventionPublishing->transition()
        ->name('info_back_auteur')
        ->from(WorkflowEnum::ADMIN_CHECKING->value)
        ->to(WorkflowEnum::AUTEUR_CHECKING->value);

    $interventionPublishing->transition()
        ->name('info_back_contributeur')
        ->from(WorkflowEnum::ADMIN_CHECKING->value)
        ->to(WorkflowEnum::AUTEUR_CHECKING->value);

    $interventionPublishing->transition()
        ->name('info_back_redacteur')
        ->from(WorkflowEnum::ADMIN_CHECKING->value)
        ->to(WorkflowEnum::ADMIN_CHECKING->value);

    $interventionPublishing->transition()
        ->name('publish')
        ->from(WorkflowEnum::ADMIN_CHECKING->value)
        ->to(WorkflowEnum::PUBLISHED->value);

    $interventionPublishing->transition()
        ->name('reject_from_auteur')
        ->from(WorkflowEnum::ADMIN_CHECKING->value)
        ->to(WorkflowEnum::DELETED->value);

    $interventionPublishing->transition()
        ->name('reject_from_admin')
        ->from(WorkflowEnum::ADMIN_CHECKING->value)
        ->to(WorkflowEnum::DELETED->value);

};
