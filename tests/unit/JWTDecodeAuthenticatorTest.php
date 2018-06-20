<?php

use Illuminate\Http\Request;
use Illuminate\Support\Debug\Dumper;
use WRPM\LaravelWPAuth\Facades\WPAuthUser;
use WRPM\LaravelWPAuth\Authenticators\JWTDecodeAuthenticator;
use Illuminate\Support\Facades\Config;
use Firebase\JWT\JWT;

class JWTDecodeAuthenticatorTest extends Orchestra\Testbench\TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->d = new Dumper();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    protected function getPackageProviders($app)
    {
        return [
            \WRPM\LaravelWPAuth\LaravelWPAuthServiceProvider::class,
            \Clockwork\Support\Laravel\ClockworkServiceProvider::class
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('wpauth.jwt_auth_secret_key', 'secret_key');
        $app['config']->set('wpauth.jwt_issuer', 'example.com');
    }

    /**
     * @test
     */
    public function testAuthSuccess()
    {
        $userMock = array(
            'id' => 22,
            'username' => 'john.doe',
            'display_name' => 'John Doe',
            'email' => 'john.doe@email.com',
            'roles' => ['administrator', 'customer'],
            'capabilities' => ['edit_post', 'delete_post']
        );
        $token = array(
            'iss' => 'example.com',
            'data' => array(
                'user' => $userMock,
            ),
        );

        $token = JWT::encode($token, 'secret_key');

        $authenticator = new JWTDecodeAuthenticator();

        $authenticator->authenticate($token);

        // $this->assertEquals($middlewareMockResponse, $middlewareResponse);
        $this->assertEquals($userMock, WPAuthUser::getUser());
    }

    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     * @expectedExceptionMessage Invalid token.
     */
    public function testAuthTokenInvalid()
    {
        $token = '123';
        $authenticator = new JWTDecodeAuthenticator();
        $authenticator->authenticate($token);

    }

    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     * @expectedExceptionMessage The iss do not match with this server.
     */
    public function testAuthTokenWrongIssuer()
    {
        $userMock = array(
            'id' => 22,
            'username' => 'john.doe',
            'display_name' => 'John Doe',
            'email' => 'john.doe@email.com',
            'roles' => ['administrator', 'customer'],
            'capabilities' => ['edit_post', 'delete_post']
        );
        $token = array(
            'iss' => 'wrongdomain.com',
            'data' => array(
                'user' => $userMock,
            ),
        );

        $token = JWT::encode($token, 'secret_key');

        $authenticator = new JWTDecodeAuthenticator();
        $authenticator->authenticate($token);

    }

    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     * @expectedExceptionMessage User ID not found in the token.
     */
    public function testAuthTokenHasNoUserId()
    {
        $userMock = array(
            'username' => 'john.doe',
            'display_name' => 'John Doe',
            'email' => 'john.doe@email.com',
            'roles' => ['administrator', 'customer'],
            'capabilities' => ['edit_post', 'delete_post']
        );
        $token = array(
            'iss' => 'example.com',
            'data' => array(
                'user' => $userMock,
            ),
        );

        $token = JWT::encode($token, 'secret_key');

        $authenticator = new JWTDecodeAuthenticator();
        $authenticator->authenticate($token);

    }
}
