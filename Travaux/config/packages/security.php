<?php

use AcMarche\Travaux\Entity\Security\User;
use AcMarche\Travaux\Security\Authenticator\TravauxAuthenticator;
use AcMarche\Travaux\Security\Authenticator\TravauxLdapAuthenticator;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Ldap\LdapInterface;
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $security) {
    $security->provider('travaux_user_provider', [
        'entity' => [
            'class' => User::class,
            'property' => 'username',
        ],
    ]);

    $authenticators = [TravauxAuthenticator::class];
    if (interface_exists(LdapInterface::class)) {
        $authenticators[] = TravauxLdapAuthenticator::class;
        $main['form_login_ldap'] = [
            'service' => Ldap::class,
            'check_path' => 'app_login',
        ];
    }

    // @see Symfony\Config\Security\FirewallConfig
    $main = [
        'provider' => 'travaux_user_provider',
        'logout' => [
            'path' => 'app_logout',
        ],
        'form_login' => [],
        'entry_point' => TravauxAuthenticator::class,
        'custom_authenticators' => $authenticators,
        'login_throttling' => [
            'max_attempts' => 6, // per minute...
        ],
        'remember_me' => [
            'secret' => '%kernel.secret%',
            'lifetime' => 604800,
            'path' => '/',
            'always_remember_me' => true,
        ],
    ];

    $security->firewall('main', $main)
        ->switchUser();
};
