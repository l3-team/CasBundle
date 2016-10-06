<?php

namespace L3\Bundle\CasBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class CasToken extends AbstractToken {

    public function __construct(array $roles = array()) {
        parent::__construct($roles);

        $this->setAuthenticated(count($roles) > 0);
    }

    /**
     * Returns the user credentials.
     *
     * @return mixed The user credentials
     */
    public function getCredentials() {
        return '';
    }

} 