<?php

use AcMarche\Avaloir\Location\LocationReverseInterface;
use AcMarche\Avaloir\Location\OpenStreetMapReverse;
use AcMarche\Avaloir\Namer\DirectoryNamer;
use AcMarche\Travaux\Security\Ldap\LdapIntranet;
use AcMarche\Travaux\Service\Option;
use Liip\ImagineBundle\Service\FilterService;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Ldap\Adapter\ExtLdap\Adapter;
use Symfony\Component\Ldap\LdapInterface;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('locale', 'fr');
    $parameters->set('ac_marche_travaux.upload.directory', "%kernel.project_dir%/public/files");
    $parameters->set('ac_marche_avaloir.upload.directory:', "%kernel.project_dir%/public/avaloirs");
    $parameters->set('ac_marche_travaux_dir_public', "%kernel.project_dir%/public");
    $parameters->set('ac_marche_travaux.download.directory', "/files");
    $parameters->set('acmarche_travaux.elastic.host', '%env(ELASTIC_HOST)%');
    $parameters->set('acmarche_travaux.elastic.index', '%env(ELASTIC_INDEX)%');
    $parameters->set('router.request_context.host', '%env(APP_URL)%');
    $parameters->set('router.request_context.scheme', 'http');
    $parameters->set('ac_marche_avaloir_destinataire', '%env(AVALOIR_EMAIL)%');
    $parameters->set(Option::LDAP_DN, '%env(LDAP_STAFF_BASE)%');
    $parameters->set(Option::LDAP_USER, '%env(LDAP_STAFF_ADMIN)%');
    $parameters->set(Option::LDAP_PASSWORD, '%env(LDAP_STAFF_PWD)%');

    /*
     * Pour envoie de mail en mode console
     */
    $parameters->set('router.request_context.scheme', '%env(INTRANET_HTTP_SCHEME)%');
    $parameters->set('router.request_context.host', '%env(INTRANET_HTTP_HOST)%');

    $services = $containerConfigurator->services();

    $services = $services
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->bind('$apiKeyGoogle', '%env(GOOGLE_KEY_API)%');

    $services->load('AcMarche\Travaux\\', __DIR__.'/../src/*')
        ->exclude([__DIR__.'/../src/{Entity,Tests}']);

    $services->load('AcMarche\Avaloir\\', __DIR__.'/../../Avaloir/src/*')
        ->exclude([__DIR__.'/../../Avaloir/src/{Entity,Tests}']);

    $services->load('AcMarche\Stock\\', __DIR__.'/../../Stock/src')
        ->exclude([__DIR__.'/../../Stock/src/{Entity,Tests}']);

    //register by default reverse service
    $services->alias(LocationReverseInterface::class, OpenStreetMapReverse::class);

    $services->alias(FilterService::class, 'liip_imagine.service.filter');

    $services->set(DirectoryNamer::class)
        ->public();


    if (interface_exists(LdapInterface::class)) {
        /* $services = $services
             ->set(Symfony\Component\Ldap\Ldap::class)
             ->args(['@Symfony\Component\Ldap\Adapter\ExtLdap\Adapter'])
             ->tag('ldap');*/
        $services = $services
            ->set(Adapter::class)
            ->args(
                [
                    [
                        'host' => '%env(LDAP_STAFF_URL)%',
                        'port' => 636,
                        'encryption' => 'ssl',
                        'options' => [
                            'protocol_version' => 3,
                            'referrals' => false,
                        ],
                    ],
                ]
            );
        $services = $services->set(LdapIntranet::class)
            ->arg('$adapter', service(Adapter::class))
            ->tag('ldap'); //necessary for new LdapBadge(LdapMercredi::class)
    }

};
