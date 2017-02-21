Symfony 2/3 Cas Bundle

This bundle is a dependancy based wrapper for the classic jasig/phpCAS library. 

Supports Single Sign Out (no support in BeSimpleSSoBundle).

Installation
---
Install the Bundle by adding this line to your composer.json :
```
"l3/cas-bundle": "~1.0"
```
Then 
 ```
$ composer update
 ```
 
Next, add the Bundle in AppKernel.php

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

Bundle Configuration
---
Add the l3_cas parameters in your config file (parameters.yml, config.yml, config_prod.yml...) :
```
l3_cas:
    host: cas-test.univ-lille3.fr               # Cas Server
    path: ~                                             # App path if not in root (eg. cas.test.com/cas)
    port: 443                                          # Server port
    ca: false                                           # SSL Certificate
    handleLogoutRequest: true                           # Single sign out activation (default: false)
    casLogoutTarget: https://ent-test.univ-lille3.fr    # Redirect path after logout
    force: true                                         # Allows cas check mode and not force, user : __NO_USER__ if not connected (If force false, Single sign out cant work).
```
Note: remember, you can use config_dev.yml to specify a test cas server in dev environment.



Then configure the firewall :
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
    host: cas-test.univ-lille3.fr               # Cas Server
    path: ~                                             # App path if not in root (eg. cas.test.com/cas)
    port: 443                                          # Server port
    ca: false                                           # SSL Certificate
    handleLogoutRequest: true                           # Single sign out activation (default: false)
    casLogoutTarget: https://ent-test.univ-lille3.fr    # Redirect path after logout
    force: false                                         # Allows cas check mode and not force, user : __NO_USER__ if not connected (If force false, Single sign out cant work).
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
3. add parameters cas_host and casLoginTarget in your files app/config/parameters.yml.dist and app/config/parameters.yml NOT under l3_cas
```
        cas_login_target: httpi://your_web_path_application.com
        cas_host: cas-test.univ-lille3.fr
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
5. you can use the route /login in order to call the cas login page and redirect to your application, you become connected :)

Configuration of the Single Sign Out
---
In order to use the Single Sign Out, it is recommanded to disable PHP Sessions in Symfony2 :
```
# app/config/config.yml
framework:
    # ...
    session:
        handler_id:  ~
        save_path: ~
```
**Information :** The bundle checks with PHPCas to detect some disconnections requests not fully implemented by PHPCAS (see L3\Bundle\CasBundle\Security\CasListener::checkHandleLogout() for more details)

UserProvider
---
For LDAP users, you can use the LdapUserBundle (branch ou=people) or LdapUdlUserBundle (branch ou=accounts).
You can use the simple UidUserBundle which only returns the uid.
You can also use FOSUserBundle... (Documentation soon)


Logout route
---
If you want use **/logout** route, you can add this in your **routing.yml** :
```
l3_logout:
    path:     /logout
    defaults: { _controller: L3CasBundle:Logout:logout }
```
