<?php

use Symfony\Config\TwigConfig;

return static function (TwigConfig $twig) {
    $twig
        ->formThemes(['bootstrap_5_layout.html.twig'])
        ->path('%kernel.project_dir%/src/AcMarche/Travaux/templates', 'AcMarcheTravaux')
        ->path('%kernel.project_dir%/src/AcMarche/Stock/templates', 'AcMarcheStock')
        ->path('%kernel.project_dir%/src/AcMarche/Avaloir/templates', 'AcMarcheAvaloir')
        ->global('bootcdn')->value('https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css');
};
