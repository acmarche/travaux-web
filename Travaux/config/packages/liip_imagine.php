<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('liip_imagine', ['resolvers' => ['default' => ['web_path' => null]]]);

    $containerConfigurator->extension(
        'liip_imagine',
        [
            'filter_sets' => [
                'cache' => null,
                'actravaux_thumb' => [
                    'quality' => 95,
                    'filters' => ['thumbnail' => ['size' => [250, 188], 'mode' => 'outbound', 'auto_rotate' => ['~']]],
                ],
                'aval_thumb' => [
                    'quality' => 95,
                    'filters' => ['thumbnail' => ['size' => [150, 70], 'mode' => 'outbound', 'auto_rotate' => ['~']]],
                ],
                'actravaux_zoom' => [
                    'quality' => 95,
                    'filters' => ['thumbnail' => ['size' => [1024, 768], 'mode' => 'inset', 'auto_rotate' => ['~']]],
                ],
                'avaloir_thumb' => [
                    'quality' => 95,
                    'filters' => ['thumbnail' => ['size' => [800, 600], 'mode' => 'outbound', 'auto_rotate' => ['~']]],
                ],
                'avaloir_heighten_filter' => [
                    'quality' => 95,
                    'filters' => ['relative_resize' => ['heighten' => 600], 'auto_rotate' => ['~']],
                ],
                'avaloir_smartphone' => [
                    'quality' => 85,
                    'filters' => ['relative_resize' => ['heighten' => 600], 'auto_rotate' => ['~']],
                ],
                'avaloir_heighten_small' => [
                    'quality' => 95,
                    'filters' => ['relative_resize' => ['heighten' => 200], 'auto_rotate' => ['~']],
                ],
            ],
        ]
    );
};

