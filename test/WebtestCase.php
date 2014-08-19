<?php

namespace Renegare\Scoauth\Test;

use Silex\Application;
use Symfony\Component\HttpKernel\Client;
use Symfony\Component\BrowserKit\Cookie;

class WebTestCase extends \Silex\WebTestCase {

    private $mockLogger;

    public function createClient(array $server = [], Application $app=null)
    {
        return new Client($this->app, $server);
    }

    public function createApplication($disableExceptionHandler = true)
    {
        $app = new Application();
        $this->configureApplication($app);

        if($disableExceptionHandler) {
            $app['exception_handler']->disable();
        }

        $app['debug'] = true;
        $app['session.test'] = true;

        return $app;
    }

    protected function configureApplication(Application $app) {
        $app['logger'] = $this->getMockLogger();

        $app->register(new \Silex\Provider\SessionServiceProvider);
        $app->register(new \Silex\Provider\SecurityServiceProvider);
        $app->register(new \Renegare\Scoauth\OAuthClientServiceProvider);

        $app['security.firewalls'] = [
            'healthcheck' => [
                'pattern' => '^/healthcheck',
                'anonymous' => true,
                'stateless' => true
            ],

            'app' => [
                'pattern' => '^/',
                'scoauth' => true
            ]
        ];

        $app->get('/healthcheck', function(){
            return 'All Good!';
        });

        $app->get('/proteted-uri', function(){
            return 'All Good!';
        });
    }

    public function getMockLogger() {
        if(!$this->mockLogger) {
            $this->mockLogger = $this->getMock('Psr\Log\LoggerInterface');
        }

        return $this->mockLogger;
    }
}
