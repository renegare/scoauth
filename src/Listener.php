<?php

namespace Renegare\Scoauth;

use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;

class Listener implements ListenerInterface, LoggerInterface {
    use LoggerTrait;

    protected $firewallName;
    protected $securityContext;
    protected $client;

    /**
     * @param string $firewallName
     */
    public function __construct($firewallName, SecurityContextInterface $securityContext, ClientInterface $client) {
        $this->firewallName = $firewallName;
        $this->securityContext = $securityContext;
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(GetResponseEvent $event) {
        $this->debug('> Request picked up by listener');
        $request = $event->getRequest();
        $token = $this->securityContext->getToken();

        if(!$token || !($token instanceof Token)) {
            $this->debug('> Unauthorized request ... authentication via oauth required!');
            $response = new RedirectResponse($this->client->getAuthUrl());
            $event->setResponse($response);
        }
    }
}
