<?php

namespace L3\Bundle\CasBundle\Security;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

/**
 * Handles successfully logging out of symfony.
 *
 * Forces logging out of cas if required.
 */
class CasLogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    protected $casConfig;

    /*
     * Create handler instance.
     *
     * @param array $cas_config
     *   Cas config parameter.
     */
    public function __construct(array $cas_config)
    {
        $this->casConfig = $cas_config;
    }

    /**
     * {@inheritdoc}
     */
    public function onLogoutSuccess(Request $request): Response
    {
        if(!empty($this->casConfig['casLogoutTarget'])) {
            \phpCas::logoutWithRedirectService($this->casConfig['casLogoutTarget']);
        } else {
            \phpCAS::logout();
        }
    }
}
