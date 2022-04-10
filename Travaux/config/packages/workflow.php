<?php

use AcMarche\Travaux\Entity\Intervention;
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework) {
    $interventionPublishing = $framework->workflows()->workflows('intervention_publication');
    $interventionPublishing
        ->type('state_machine')
        ->supports([Intervention::class])
        ->initialMarking(['auteur_checking']);

    $interventionPublishing->auditTrail()->enabled(true);
    $interventionPublishing->markingStore()
        ->type('method')
        ->property('currentPlace');

    $interventionPublishing->place()->name('auteur_checking');
    $interventionPublishing->place()->name('redacteur');
    $interventionPublishing->place()->name('admin_checking');
    $interventionPublishing->place()->name('deleted');
    $interventionPublishing->place()->name('published');

    $interventionPublishing->transition()
        ->name('auteur_accept')
        ->from(['auteur_checking'])
        ->to(['admin_checking']);

    $interventionPublishing->transition()
        ->name('info_back_auteur')
        ->from(['admin_checking'])
        ->to(['auteur_checking']);

    $interventionPublishing->transition()
        ->name('info_back_contributeur')
        ->from(['admin_checking'])
        ->to(['auteur_checking']);

    $interventionPublishing->transition()
        ->name('info_back_redacteur')
        ->from(['admin_checking'])
        ->to(['admin_checking']);

    $interventionPublishing->transition()
        ->name('publish')
        ->from(['admin_checking'])
        ->to(['published']);

    $interventionPublishing->transition()
        ->name('reject_from_auteur')
        ->from(['admin_checking'])
        ->to(['deleted']);

    $interventionPublishing->transition()
        ->name('reject_from_admin')
        ->from(['admin_checking'])
        ->to(['deleted']);

};
