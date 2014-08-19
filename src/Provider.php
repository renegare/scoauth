<?php

namespace Renegare\Scoauth;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class Provider implements AuthenticationProviderInterface {

    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token) {
        $token->setAuthenticated(true);
        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TokenInterface $token) {
        return $token instanceof Token;
    }
}
