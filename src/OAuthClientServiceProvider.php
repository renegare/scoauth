<?php

namespace Renegare\Scoauth;

use Silex\Application;
use Silex\ServiceProviderInterface;

class OAuthClientServiceProvider implements ServiceProviderInterface {

    public function register(Application $app) {
        $app['security.authentication_listener.factory.scoauth'] = $app->protect(function ($name, $options) use ($app) {

            $providerServiceName = 'security.authentication_provider.'.$name.'.scoauth';
            if(!isset($app[$providerServiceName])) {
                $app[$providerServiceName] = $app->share(function () use ($app, $name) {
                    return new Provider;
                });
            }

            $app['security.authentication_listener.'.$name.'.scoauth'] = $app->share(function () use ($app, $name) {

                $client = $app['scoauth.api.client'];

                $listener = new Listener($name, $app['security'], $app['security.authentication_manager'], $client);

                if(isset($app['logger']) && $app['logger']) {
                    $listener->setLogger($app['logger']);
                }

                $uri = $client->getRedirectUri();
                $pathName = 'security_scoauth_callback_' . $name;
                $this->addFakeRoute('GET', $uri, $pathName);

                return $listener;
            });

            return array(
                // the authentication provider id
                'security.authentication_provider.'.$name.'.scoauth',
                // the authentication listener id
                'security.authentication_listener.'.$name.'.scoauth',
                // the entry point id
                null,
                // the position of the listener in the stack
                'pre_auth'
            );
        });
    }

    public function boot(Application $app)
    {
        foreach ($this->fakeRoutes as $route) {
            list($method, $pattern, $name) = $route;
            $app->$method($pattern)->run(null)->bind($name);
        }
    }

    public function addFakeRoute($method, $pattern, $name)
    {
        $this->fakeRoutes[] = array($method, $pattern, $name);
    }
}
