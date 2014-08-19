<?php

namespace Renegare\Scoauth;

interface ClientInterface {

    /**
     * get the full url to redirect a user to authenticate (urlencoded!)
     * @return string
     */
    public function getAuthUrl();
}
