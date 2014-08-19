<?php

namespace Renegare\Scoauth;

use Silex\Application;
use Silex\ServiceProviderInterface;

class OAuthClientServiceProvider implements ServiceProviderInterface {

    public function register(Application $app) {
        $app['security.authentication_listener.factory.scoauth'] = $app->protect(function ($name, $options) use ($app) {
            $app['security.authentication_provider.'.$name.'.scoauth'] = $app->share(function () use ($app, $name) {
                return null;
            });

            $app['security.authentication_listener.'.$name.'.scoauth'] = $app->share(function () use ($app, $name) {

                $listener = new Listener($name, $app['security'], $app['scoauth.api']);

                if(isset($app['logger']) && $app['logger']) {
                    $listener->setLogger($app['logger']);
                }

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

    public function boot(Application $app) {}
}
