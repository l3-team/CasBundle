<?php

namespace L3\Bundle\CasBundle\Security;


use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CasListener {
    protected $tokenStorage;
    protected $authenticationManager;
    protected $config;

    public function __construct(TokenStorageInterface $tokenStorage, AuthenticationManagerInterface $authenticationManager, $config) {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->config = $config;
    }

    public function __invoke(RequestEvent $event) {
        if(!isset($_SESSION)) session_start();

        \phpCAS::setDebug(false);

        \phpCAS::client(CAS_VERSION_2_0, $this->getParameter('host'), $this->getParameter('port'), is_null($this->getParameter('path')) ? '' : $this->getParameter('path'), true);

        if(is_bool($this->getParameter('ca')) && $this->getParameter('ca') == false) {
            \phpCAS::setNoCasServerValidation();
        } else {
            \phpCAS::setCasServerCACert($this->getParameter('ca'));
        }

        if($this->getParameter('handleLogoutRequest')) {
            if($event->getRequest()->request->has('logoutRequest')) {
                $this->checkHandleLogout($event);
            }
            $logoutRequest = $event->getRequest()->request->get('logoutRequest');

            \phpCAS::handleLogoutRequests(true);
        } else {
            \phpCAS::handleLogoutRequests(false);
        }

        // si le mode gateway est activé..
        if ($this->getParameter('gateway')) {
            
            // .. code de pierre pelisset (pour les applis existantes...)
            
            if($this->getParameter('force')) {
                \phpCAS::forceAuthentication();
                $force = true;
            } else {
                $force = false;
                if(!isset($_SESSION['cas_user'])) {
                    $auth = \phpCAS::checkAuthentication();
                    if($auth) {
                        $_SESSION['cas_user'] = \phpCAS::getUser();
                        $_SESSION['cas_attributes'] = \phpCAS::getAttributes();
                    }
                    else $_SESSION['cas_user'] = false;
                }
            }
            if(!$force) {
                if(!$_SESSION['cas_user']) {
                    $token = new CasToken(array('ROLE_ANON'));
                    $token->setUser('__NO_USER__');
                } else {
                    $token = new CasToken();
                    $token->setUser($_SESSION['cas_user']);
                    $token->setAttributes($_SESSION['cas_attributes']);
                }
                $this->tokenStorage->setToken($this->authenticationManager->authenticate($token));
                return;
            }
            
        } else { 
        
            // .. sinon code de david .. pour les api rest / microservices et donc le nouvel ent ulille en view js notamment
            
            if($this->getParameter('force')) {
                \phpCAS::forceAuthentication();
            } else {
                $authenticated = false;          
                if($this->getParameter('gateway')) {
                    $authenticated = \phpCAS::checkAuthentication();
                } else {
                    $authenticated = \phpCAS::isAuthenticated();
                }
                if ( (!isset($_SESSION['cas_user'])) || ( (isset($_SESSION['cas_user'])) && ($_SESSION['cas_user'] == false) ) ) { 
                    if($authenticated) {
                        $_SESSION['cas_user'] = \phpCAS::getUser();
                        $_SESSION['cas_attributes'] = \phpCAS::getAttributes();
                        $token = new CasToken();
                        $token->setUser($_SESSION['cas_user']);
                        $token->setAttributes($_SESSION['cas_attributes']);
                    } else {
                        //$_SESSION['cas_user'] = false;
                        $token = new CasToken(array('ROLE_ANON'));
                        $token->setUser('__NO_USER__');
                    }
                    $this->tokenStorage->setToken($this->authenticationManager->authenticate($token));
                    return;
                }
            } 
        }

        $token = new CasToken();
        $token->setUser(\phpCAS::getUser());
	$token->setAttributes(\phpCAS::getAttributes());

        try {
            $authToken = $this->authenticationManager->authenticate($token);
            $this->tokenStorage->setToken($authToken);
        } catch(AuthenticationException $failed) {
            $response = new Response();
            $response->setStatusCode(403);
            $event->setResponse($response);
        }
    }

    public function getParameter($key) {
        if(!array_key_exists($key, $this->config)) {
            throw new InvalidConfigurationException('l3_cas.' . $key . ' is not defined');
        }
        return $this->config[$key];
    }

    /**
     * Cette fonction sert à vérifier le global logout, PHPCAS n'arrive en effet pas à le gérer étrangement dans Symfony2
     * @param GetResponseEvent $event
     */
    public function checkHandleLogout(GetResponseEvent $event) {
        // Récupération du paramètre
        $logoutRequest = $event->getRequest()->request->get('logoutRequest');
        // Les chaines recherchés
        $open = '<samlp:SessionIndex>';
        $close = '</samlp:SessionIndex>';

        // Isolation de la clé de session
        $begin = strpos($logoutRequest, $open);
        $end = strpos($logoutRequest, $close, $begin);
        $sessionID = substr($logoutRequest, $begin+strlen($open), $end-strlen($close)-$begin+1);

        // Changement de session et destruction pour forcer l'authentification CAS à la prochaine visite
        session_id($sessionID);
        session_destroy();
    }
}
