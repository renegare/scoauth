<?php

namespace Renegare\Scoauth\Test;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

class ListenerTest extends WebtestCase {

    protected $mockClientApi;

    protected function configureApplication(Application $app) {
        parent::configureApplication($app);

        $app->get('/protected-uri', function(){
            return 'All Good!';
        });

        // mock out dependencies
        $this->mockClientApi = $this->getMock('Renegare\Scoauth\ClientInterface');

        $app['scoauth.api.client'] = $this->mockClientApi;
    }

    public function testAnonymousUserFlow() {
        $expectedRedirecUri = '/security/scoauth/cb';
        $expectedAuthUrl = sprintf('http://api.com/auth?%s', http_build_query([
            'client_id' => 1,
            'response_type' => 'code',
            'redirect_uri' => 'http://localhost/security/scoauth/cb',
            'scope' => 'all'
        ]));

        $mockToken = $this->getMockForAbstractClass('Renegare\Scoauth\Token');
        $this->mockClientApi->expects($this->any())->method('getAuthUrl')->will($this->returnValue($expectedAuthUrl));
        $this->mockClientApi->expects($this->any())->method('getRedirectUri')->will($this->returnValue($expectedRedirecUri));
        $this->mockClientApi->expects($this->any())->method('createToken')->will($this->returnValue($mockToken));

        // redirect to api
        $client = $this->createClient();
        $client->request('GET', '/protected-uri');
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertEquals(sprintf('http://api.com/auth?%s', http_build_query([
            'client_id' => 1,
            'response_type' => 'code',
            'redirect_uri' => 'http://localhost/security/scoauth/cb',
            'scope' => 'all'
        ])), $response->getTargetUrl());

        // handle auth code
        $client->request('GET', '/security/scoauth/cb', ['code' => 'test-auth-code']);
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_FOUND, $response->getStatusCode());

        // redirect to original target
        $client->followRedirect();
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('All Good!', $response->getContent());
    }
}
