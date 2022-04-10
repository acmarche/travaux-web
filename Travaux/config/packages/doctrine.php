<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension(
        'doctrine',
        [
            'orm' => [
                'mappings' => [
                    'AcMarche\Travaux' => [
                        'is_bundle' => false,
                        'type' => 'attribute',
                        'dir' => '%kernel.project_dir%/src/AcMarche/Travaux/src/Entity',
                        'prefix' => 'AcMarche\Travaux',
                        'alias' => 'AcMarche\Travaux',
                    ],
                    'AcMarche\Avaloir' => [
                        'is_bundle' => false,
                        'type' => 'attribute',
                        'dir' => '%kernel.project_dir%/src/AcMarche/Avaloir/src/Entity',
                        'prefix' => 'AcMarche\Avaloir',
                        'alias' => 'AcMarche\Avaloir',
                    ],
                    'AcMarche\Stock' => [
                        'is_bundle' => false,
                        'type' => 'attribute',
                        'dir' => '%kernel.project_dir%/src/AcMarche/Stock/src/Entity',
                        'prefix' => 'AcMarche\Stock',
                        'alias' => 'AcMarche\Stock',
                    ],
                ],
            ],
        ]
    );
};
