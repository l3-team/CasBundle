<?php

namespace L3\Bundle\CasBundle\Security;


use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;

class CasProvider implements AuthenticationProviderInterface {
    private $userProvider;
    private $config;

    public function __construct(UserProviderInterface $userProvider) {
        $this->userProvider = $userProvider;
    }

    public function authenticate(TokenInterface $token) {
        $user = $this->userProvider->loadUserByUsername($token->getUsername());

        $authenticatedToken = new CasToken($user->getRoles());
        $authenticatedToken->setUser($user);
	$authenticatedToken->setAttributes($token->getAttributes());


        return $authenticatedToken;
    }

    public function supports(TokenInterface $token) {
        return $token instanceof CasToken;
    }
} 
