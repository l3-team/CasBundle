Bundle CAS for Symfony2.

Allow wrappe PHPCas with authentication Symfony2. Support the Single Sign Out in contrary of BeSimpleSSoBundle

Installation of Bundle.
---
Install the Bundle with add this line in your require in composer.json :
```
"l3/cas-bundle": "~1.0"
```
Launch the command **composer update** pour install the package and add the Bundle in AppKernel.php
```
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new L3\Bundle\CasBundle\L3CasBundle(),
        );

        // ...
    }

    // ...
}
```

Configuration of the bundle
---
In the configuration files (parameters.yml, config.yml, config_prod.yml...), configure your cas server :
```
l3_cas:
    host: cas-test.univ-lille3.fr                       # Serveur CAS
    path: ~                                             # Chemin de l'application CAS si elle ne se trouve pas à la racine
    port: 443                                           # Port du serveur CAS
    ca: false                                           # Définition d'un certificat SSL pour le serveur CAS
    handleLogoutRequest: true                           # Activiation du Single Sign Out (défaut: false)
    casLogoutTarget: https://ent-test.univ-lille3.fr    # Page de redirection après la déconnexion de l'application
    force: false                                        # Permet de checker et non de forcer le CAS, utilisateur : __NO_USER__ si non connecté (Information: Si force désactivé, le Single Sign Out peut ne plus fonctionner).
```

Puis configurer le pare-feu :
```
# app/config/security.yml
security:
    providers:
            # ...


    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt|error)|css|images|js)/
            security: false

        l3_firewall:
            pattern: ^/
            security: true
            cas: true # Activation du CAS
```

Configuration of the Single Sign Out
---
In order to use the Single Sign Out, it is recommanded to disable the system of the sessions PHP in Symfony2 :
```
# app/config/config.yml
framework:
    # ...
    session:
        handler_id:  ~
        save_path: ~
```
**Information :** The bundle check complementary with PHPCas to detect some disconnections requests not fully implemented by PHPCAS (see L3\Bundle\CasBundle\Security\CasListener::checkHandleLogout() for more details)

UserProvider
---
For LDAP users, you can use the LdapUserBundle
