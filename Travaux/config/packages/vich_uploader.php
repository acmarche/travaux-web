<?php

use AcMarche\Avaloir\Namer\DirectoryNamer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension(
        'vich_uploader',
        [
            'mappings' => [
                'taxes' => [
                    'uri_prefix' => '/files/interventions',
                    'upload_destination' => '%kernel.project_dir%/public/files/interventions',
                    'namer' => 'vich_uploader.namer_uniqid',
                    'inject_on_load' => false,
                ],
                'avaloir_image' => [
                    'uri_prefix' => '/avaloirs',
                    'upload_destination' => '%kernel.project_dir%/public/avaloirs',
                    'namer' => 'vich_uploader.namer_uniqid',
                    'directory_namer' => ['service' => DirectoryNamer::class],
                    'inject_on_load' => false,
                ],
                'item_image' => [
                    'uri_prefix' => '/items',
                    'upload_destination' => '%kernel.project_dir%/public/items',
                    'namer' => 'vich_uploader.namer_uniqid',
                    'directory_namer' => ['service' => DirectoryNamer::class],
                    'inject_on_load' => false,
                ],
            ],
        ]
    );
};