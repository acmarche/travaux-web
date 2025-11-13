<?php

use AcMarche\Travaux\Entity\Security\User;
use AcMarche\Travaux\Security\Authenticator\AppTravauxAuthenticator;
use AcMarche\Travaux\Security\Authenticator\AppTravauxLdapAuthenticator;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Ldap\LdapInterface;
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $security): void {
    $security
        ->provider('apptravaux_user_provider')
        ->entity()
        ->class(User::class)
        ->managerName('default')
        ->property('username');

    $security
        ->firewall('dev')
        ->pattern('^/(_(profiler|wdt)|css|images|js)/')
        ->security(false);

    $mainFirewall = $security
        ->firewall('main')
        ->lazy(true);

    $mainFirewall->switchUser();

    $mainFirewall
        ->formLogin()
        ->loginPath('app_login')
        ->rememberMe(true)
        ->enableCsrf(true);

    $mainFirewall
        ->logout()
        ->path('app_logout');

    $authenticators = [AppTravauxAuthenticator::class];

    if (interface_exists(LdapInterface::class)) {
        $authenticators[] = AppTravauxLdapAuthenticator::class;
        $mainFirewall->formLoginLdap([
            'service' => Ldap::class,
            'check_path' => 'app_login',
        ]);
    }

    $mainFirewall
        ->customAuthenticators($authenticators)
        ->provider('apptravaux_user_provider')
        ->entryPoint(AppTravauxAuthenticator::class)
        ->loginThrottling()
        ->maxAttempts(6)
        ->interval('15 minutes');

    $mainFirewall
        ->rememberMe([
            'secret' => '%kernel.secret%',
            'lifetime' => 604800,
            'path' => '/',
            'always_remember_me' => true,
        ]);

    $security->roleHierarchy('ROLE_TRAVAUX_ADMIN', ['ROLE_ALLOWED_TO_SWITCH']);
};
