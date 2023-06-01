<?php

use AcMarche\Travaux\Entity\Security\User;
use AcMarche\Travaux\Security\Authenticator\TravauxAuthenticator;
use AcMarche\Travaux\Security\Authenticator\TravauxLdapAuthenticator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Ldap\LdapInterface;
use Symfony\Config\SecurityConfig;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('security', [
        'password_hashers' => [
            User::class => [
                'algorithm' => 'auto',
            ],
        ],
    ]);

    $containerConfigurator->extension(
        'security',
        [
            'providers' => [
                'travaux_user_provider' => [
                    'entity' => [
                        'class' => User::class,
                        'property' => 'username',
                    ],
                ],
            ],
        ]
    );

    $authenticators = [TravauxAuthenticator::class];

    $main = [
        'provider' => 'travaux_user_provider',
        'logout' => [
            'path' => 'app_logout',
        ],
        'form_login' => [],
        'entry_point' => TravauxAuthenticator::class,
        'switch_user' => true,
        'login_throttling' => [
            'max_attempts' => 6, //per minute...
        ],
    ];

    if (interface_exists(LdapInterface::class)) {
        $authenticators[] = TravauxLdapAuthenticator::class;
        $main['form_login_ldap'] = [
            'service' => Ldap::class,
            'check_path' => 'app_login',
        ];
    }

    $main['custom_authenticator'] = $authenticators;

    $containerConfigurator->extension(
        'security',
        [
            'firewalls' => [
                'main' => $main,
            ],
        ]
    );

    $containerConfigurator->extension(
        'security',
        [
            'role_hierarchy' => [
                'ROLE_FINANCE_FRAIS_ADMIN' => ['ROLE_FINANCE_FRAIS'],
            ],
        ]
    );
};

return static function (SecurityConfig $security) {
    $security
        ->provider('travaux_user_provider')
        ->entity()
        ->class(User::class)
        ->property('username');

    $security->passwordHasher(User::class, [
            'algorithm' => 'auto',
        ]
    );
};
