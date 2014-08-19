<?php

namespace Renegare\Scoauth;

use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;

class Listener implements ListenerInterface, LoggerInterface {
    use LoggerTrait;

    protected $firewallName;
    protected $securityContext;
    protected $client;
    protected $authManager;

    /**
     * @param string $firewallName
     * @param SecurityContextInterface $securityContext
     * @param AuthenticationManagerInterface $authManager
     * @param ClientInterface $client
     */
    public function __construct($firewallName, SecurityContextInterface $securityContext, AuthenticationManagerInterface $authManager, ClientInterface $client) {
        $this->firewallName = $firewallName;
        $this->securityContext = $securityContext;
        $this->client = $client;
        $this->authManager = $authManager;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(GetResponseEvent $event) {
        $this->debug('> Request picked up by listener');
        $token = $this->securityContext->getToken();

        if($token && $token instanceof Token) {
            $this->info('User appears to be logged in already. #Noop', ['token' => $token->getAttributes()]);
            $this->client->setToken($token);
        } else {
            $request = $event->getRequest();
            $pathInfo = $request->getPathInfo();
            if($this->requiresAuthExcange($request)) {
                $code = $request->query->get('code');
                $this->debug('> Auth code recieved', ['path' => $pathInfo, 'code' => $code]);
                $token = $this->client->createToken($code);
                $token = $this->authManager->authenticate($token);
                $this->securityContext->setToken($token);
                $this->debug('Token authenticated', ['token' => $token]);

                $session = $request->getSession();
                $originalRequestPath = $session->get('_scoauth_original_path', '/');
                $session->remove('_scoauth_original_path');
                $response = new RedirectResponse($originalRequestPath);
            } else {
                $this->debug('> Unauthorized request ... authentication via oauth required!', ['path' => $pathInfo]);
                $request->getSession()->set('_scoauth_original_path', $request->getPathInfo());
                $response = new RedirectResponse($this->client->getAuthUrl());
            }

            $event->setResponse($response);
        }
    }

    protected function requiresAuthExcange(Request $request) {
        return $request->isMethod('GET') && $request->query->get('code', false) && $request->getPathInfo() === $this->client->getRedirectUri();
    }
}
