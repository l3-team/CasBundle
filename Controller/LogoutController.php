<?php

namespace L3\Bundle\CasBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LogoutController extends AbstractController {
    public function logoutAction() {
        if(array_key_exists('casLogoutTarget', $this->container->getParameter('cas'))) {
            \phpCas::logoutWithRedirectService($this->container->getParameter('cas')['casLogoutTarget']);
        } else {
            \phpCAS::logout();
        }
    }
} 
