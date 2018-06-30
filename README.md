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
    force: true                                         # Permet de checker et non de forcer le CAS, utilisateur : __NO_USER__ si non connecté (Information: Si force désactivé, le Single Sign Out peut ne plus fonctionner).
```

Next configure the firewall :
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

If you want use the anonymous page :
---
1. set **force: false** in app/config/parameters.yml
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
2. set **default: anonymous** in app/config/security.yml
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

        default:
            anonymous: ~
```
3. add a variable casLoginTarget in your files app/config/parameters.yml.dist and app/config/parameters.yml under l3_cas
```
        cas_login_target: https://your_web_path_application.com
```
4. create a login route and force route in your DefaultController in your application:
```
/**
 * @Route("/login", name="login")
 */
public function loginAction() {
        $target = urlencode($this->container->getParameter('cas_login_target'));
        $url = 'https://'.$this->container->getParameter('cas_host') . '/login?service=';

        return $this->redirect($url . $target . '/force');
}


/**
 * @Route("/force", name="force")
 */
public function forceAction() {

        if (!isset($_SESSION)) {
                session_start();
        }

        session_destroy();

        return $this->redirect($this->generateUrl('home_page'));
}
```
5. you can use the route /login in order to call the cas login page and redirect to your application, you becomes connected :)

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
For LDAP users, you can use the LdapUserBundle (branch ou=people) or LdapUdlUserBundle (branch ou=accounts).
You can use the simple UidUserBundle which only returns the uid.
You can also use FOSUserBundle... like this :
//security.yml
```yml
    providers:
        chain_provider:
            chain:
                providers: [in_memory, fos_userbundle]
        in_memory:
            memory:
                users:
                    __NO_USER__:
                        password:
                        roles: ROLE_ANON
        fos_userbundle:
            id: fos_user.user_provider.username
```

Logout route
---
If you want use **/logout** route, you can add this in your **routing.yml** :
```
l3_logout:
    path:     /logout
    defaults: { _controller: L3CasBundle:Logout:logout }
```
