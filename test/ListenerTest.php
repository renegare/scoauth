<?php

namespace Renegare\Scoauth\Test;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Renegare\Scoauth\Test\WebTestCase;

class ListenerTest extends WebTestCase {

    protected $mockClientApi;

    protected function configureApplication(Application $app) {
        parent::configureApplication($app);

        $app->get('/protected-uri', function(){
            return 'All Good!';
        });

        $this->mockClientApi = $this->getMock('Renegare\Scoauth\ClientInterface');

        $app['scoauth.api'] = $this->mockClientApi;
    }

    public function testAnonymousUserFlow() {
        $expectedAuthUrl = sprintf('http://api.com/auth?%s', http_build_query([
            'client_id' => 1,
            'response_type' => 'code',
            'redirect_uri' => 'http://localhost/scoauth/security/app',
            'scope' => 'all']));
        $this->mockClientApi->expects($this->once())->method('getAuthUrl')->will($this->returnValue($expectedAuthUrl));

        $client = $this->createClient();
        $client->request('GET', '/protected-uri');
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertEquals(sprintf('http://api.com/auth?%s', http_build_query([
            'client_id' => 1,
            'response_type' => 'code',
            'redirect_uri' => 'http://localhost/scoauth/security/app',
            'scope' => 'all'])), $response->getTargetUrl());
    }
}
