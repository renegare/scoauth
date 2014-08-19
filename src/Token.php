<?php

namespace Renegare\Scoauth;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class Token extends AbstractToken {

    /**
     * presently does nothing
     * {@inheritdoc}
     */
    public function getCredentials() {
        return null;
    }
}
