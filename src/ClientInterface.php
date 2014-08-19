<?php

namespace Renegare\Scoauth;

interface ClientInterface {

    /**
     * get the full api auth url to redirect a user to authenticate (urlencoded!)
     * @return string
     */
    public function getAuthUrl();

    /**
     * get the redirect URI (just the path) of the oauth callback redirect uri
     * @return string
     */
    public function getRedirectUri();

    /**
     * create an access token containing all the information to access the api (e.g access code, expiry and refresh_code)
     * @param string $authCode
     * @return Token
     */
    public function createToken($authCode);

    /**
     * set token to be used for api requests
     * @param Token $token
     */
    public function setToken(Token $token);
}
