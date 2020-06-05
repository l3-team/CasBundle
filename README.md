Symfony Cas Bundle

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

Declaration of the Bundle in the Kernel of Symfony
---
For Symfony2 or Symfony3, add the Bundle in app/AppKernel.php

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

For Symfony4 and Symfony5, add the Bundle in config/bundles.php (if line not present)
```
<?php

return [
    ...
    L3\Bundle\CasBundle\L3CasBundle::class => ['all' => true],
    ...
];
```

Bundle Configuration
---
For Symfony2 or Symfony3, add the l3_cas parameters in your config file (parameters.yml and parameters.yml.dist) :
```
l3_cas:
    host: cas-test.univ-lille3.fr                       # Cas Server
    path: ~                                             # App path if not in root (eg. cas.test.com/cas)
    port: 443                                           # Server port
    ca: false                                           # SSL Certificate
    handleLogoutRequest: true                           # Single sign out activation (default: false)
    casLogoutTarget: https://ent-test.univ-lille3.fr    # Redirect path after logout
    force: true                                         # Allows cas check mode and not force, user : __NO_USER__ if not connected (If force false, Single sign out cant work).
    gateway: true					# Gateway mode (for use the mode gateway of the Cas Server) set to false if you use micro-services or apis rest.
```

For Symfony4 and Symfony5, add the variables in your config file (.env and .env.dist) :
```
...
###> l3/cas-bundle ###
CAS_HOST=cas-test.univ-lille3.fr     # Cas Server
CAS_PATH=~                           # App path if not in root (eg. cas.test.com/cas)
CAS_PORT=443                         # Server port
CAS_CA=false                         # SSL Certificate
CAS_HANDLE_LOGOUT_REQUEST=true       # Single sign out activation (default: false)
CAS_LOGIN_TARGET=https://server.univ-lille3.fr # Redirect path after login (when use anonymous mode)
CAS_LOGOUT_TARGET=https://ent-test.univ-lille3.fr    # Redirect path after logout
CAS_FORCE=true                       # Allows cas check mode and not force, user : __NO_USER__ if not connected (If force false, Single sign out cant work).
CAS_GATEWAY=true		     # Gateway mode (for use the mode gateway of the Cas Server) set to false if you use micro-services or apis rest.
###< l3/cas-bundle ###
...
```

And add the parameters in your config/services.yml file (under parameters) :
```
...
parameters:
    cas_login_target: '%env(string:CAS_LOGIN_TARGET)%'
    cas_logout_target: '%env(string:CAS_LOGOUT_TARGET)%'
    cas_host: '%env(string:CAS_HOST)%'
    cas_path: '%env(string:CAS_PATH)%'
    cas_gateway: '%env(bool:CAS_GATEWAY)%'

l3_cas:
    host: '%env(string:CAS_HOST)%'
    path: '%env(string:CAS_PATH)%'
    port: '%env(int:CAS_PORT)%'
    ca: '%env(bool:CAS_CA)%'
    handleLogoutRequest: '%env(bool:CAS_HANDLE_LOGOUT_REQUEST)%'
    casLogoutTarget: '%env(string:CAS_LOGOUT_TARGET)%'
    force: '%env(bool:CAS_FORCE)%'
    gateway: '%env(bool:CAS_GATEWAY)%'
...
```

Security Configuration
---
For Symfony2 or Symfony3 or Symfony4 or Symfony5, configure the firewall in the security file app/config/security.yml
```
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

Anonymous Configuration
---
Be careful that if you want use the anonymous mode, the bundle cas use the login __NO_USER__, use the security like this :
```yml
security:
    providers:
        chain_provider:
            chain:
                providers: [in_memory, your_userbundle]
        in_memory:
            memory:
                users:
                    __NO_USER__:
                        password:
                        roles: ROLE_ANON
        your_userbundle:
            id: your_userbundle
```
In Symfony4, if you use chain_provider, you should set provider name on all entry (ie l3_firewall and main) firewall (where security is active : **security: true**) in config/packages/security.yaml like this :
```
# config/packages/security.yaml
security:
    providers:
        chain_provider:
            chain:
                providers: [in_memory, your_userbundle]
        in_memory:
            memory:
                users:
                    __NO_USER__:
                        password:
                        roles: ROLE_ANON
        your_userbundle:
            id: your_userbundle

    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false

        l3_firewall:
            pattern: ^/
            security: true
            cas: true # Activation du CAS
            provider: chain_provider
            
        main:
            pattern: ^/
            security: true
            cas: true # Activation du CAS
            anonymous: true
            provider: chain_provider
```


Next set force to false in app/config/parameters.yml (for Symfony2 or Symfony3) and in config/services.yaml (for Symfony4) :
```
l3_cas:
    ...
    force: false                                         # Allows cas check mode and not force, user : __NO_USER__ if not connected (If force false, Single sign out cant work).
```

And for Symfony2 or Symfony3 set **default: anonymous** in app/config/security.yml
```
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

For Symfony4 and Symfony5, set **main: anonymous** in config/packages/security.yaml
```
security:
    providers:
            # ...


    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false

        l3_firewall:
            pattern: ^/
            security: true
            cas: true # Activation du CAS

        main:
            anonymous: ~
            pattern: ^/
            security: true
            cas: true # Activation du CAS
```

For Symfony2 or Symfony3, add parameters cas_host and cas_login_target and cas_path and cas_gateway in your files app/config/parameters.yml.dist and app/config/parameters.yml under parameters (NOT under l3_cas)
```
	...
        cas_login_target: https://your_web_path_application.com/
        cas_logout_target: https://your_web_path_application.com/
        cas_host: cas-test.univ-lille3.fr
        cas_path: ~
        cas_gateway: true
	...
```

For Symfony4 and Symfony5, add parameters cas_host and cas_login_target in your config/services.yaml under parameters (NOT under l3_cas)
```
        ...
        cas_login_target: '%env(string:CAS_LOGIN_TARGET)%'
        cas_logout_target: '%env(string:CAS_LOGIN_TARGET)%'
        cas_host: '%env(string:CAS_HOST)%'
        cas_path: '%env(string:CAS_PATH)%'
        cas_gateway: '%env(bool:CAS_GATEWAY)%'
        ...
```

For Symfony 2, Symfony 3 and Symfony 4, create a login route and force route in your DefaultController in your application:
```
/**
 * @Route("/login", name="login")
 */
public function loginAction() {
        
	$url = 'https://'.$this->container->getParameter('cas_host') . $this->container->getParameter('cas_path') . '/login?service=';
        $target = $this->container->getParameter('cas_login_target');

        return $this->redirect($url . urlencode($target . '/force'));
}


/**
 * @Route("/force", name="force")
 */
public function forceAction() {

	if ($this->container->getParameter('cas_gateway')) {
        	if (!isset($_SESSION)) {
                	session_start();
        	}

        	session_destroy();
	}

        return $this->redirect($this->generateUrl('homepage'));
}
```

For Symfony 5, create a login route and force route in your DefaultController in your application:
```
    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request) {
           $url = 'https://'.$this->getParameter('cas_host') . $this->getParameter('cas_path') . '/login?service=';
           $target = $this->getParameter('cas_login_target');
           return $this->redirect($url . urlencode($target . '/force'));
    }
    
    /**
     * @Route("/force", name="force")
     */
    public function force(Request $request) {

            if ($this->getParameter('cas_gateway')) {
                if (!isset($_SESSION)) {
                        session_start();
                }

                session_destroy();
            }

            return $this->redirect($this->generateUrl('index'));
    }
``` 

Finally you can use the route /login in order to call the cas login page and redirect to your application, then you become connected :)

Configuration of the Single Sign Out
---
In order to use the Single Sign Out, it is recommanded to disable Symfony Sessions in Symfony (so you will use the PHP native sessions).

```
# app/config/config.yml (for Symfony2 or Symfony3)
# config/packages/framework.yaml (for Symfony4 and Symfony5)
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

Logout Handler
---
In Symfony 2 and Symfony 3, setup a [logout route](#logout-route). For Symfony 4+, add the following to your **security.yml**:
```yml
security:
    providers:
            # ...


    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false

        l3_firewall:
            pattern: ^/
            security: true
            cas: true # Activation du CAS
            logout:
                path: logout
                success_handler: cas.security.logout.success_handler
```

Logout route
---
In Symfony 2 or Symfony 3, if you want use **/logout** route in order to call Logout, you can add this in your **routing.yml** :
```
l3_logout:
    path:     /logout
    defaults: { _controller: L3CasBundle:Logout:logout }
```

In Symfony 4, its recommended to use a logout `success_handler` instead of using a route. But, if you need a route you can add this in your **routes.yaml** :
```
logout:
    path: /logout
    controller: L3\Bundle\CasBundle\Controller\LogoutController::logoutAction
```

In Symfony 5, you must create a logout route in your DefaultController in your application:
```
    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction() {
        if (($this->getParameter('cas_logout_target') !== null) && (!empty($this->getParameter('cas_logout_target')))) {
            \phpCAS::logoutWithRedirectService($this->getParameter('cas_logout_target'));
        } else {
            \phpCAS::logout();
        }
    }
```

Additional Attributes
---
The Jasig Cas Server can return additional attributes in addition to the main attribute (generally uid) with the function phpCAS::getAttributes().

You can get the additional attributes in a controller with this code :
```
...
$attributes = $this->get('security.token_storage')->getToken()->getAttributes();
...
```
