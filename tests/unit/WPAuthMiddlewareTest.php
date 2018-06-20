<?php

use WRPM\LaravelWPAuth\Http\Middleware\WPAuthMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Debug\Dumper;
use WRPM\LaravelWPAuth\Authenticators\AuthenticatorContract;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class WPAuthMiddlewareTest extends Orchestra\Testbench\TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->authenticator = \Mockery::mock(AuthenticatorContract::class);
        $this->d = new Dumper();
    }

    public function tearDown()
    {
        unset($this->guzzleClient);

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
     * @test
     */
    public function testAuthSuccess()
    {

        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9iaWMubG9jYWxob3N0IiwiaWF0IjoxNTI1ODcxNjY2LCJuYmYiOjE1MjU4NzE2NjYsImV4cCI6MTUyNjQ3NjQ2NiwiZGF0YSI6eyJ1c2VyIjp7ImlkIjoiMjIifX19.t_bhXIpvhd0uTa3XxUpYRgBRLUZdx1TQcsaIuoa_J-4';
        $request = Request::create(
            // uri (string)
            '/api/configurations',
            // method (string)
            'GET',
            // parameters (array)
            [],
            // cookies (array)
            [],
            // files (array)
            [],
            // server (array)
            ['HTTP_Authorization' => 'Bearer ' . $token],
            // content (array|string|resource|null)
            null
        );

        $this->authenticator->shouldReceive('authenticate')->with($token)->times(1);

        $middlewareMockResponse = 'Hello from WP Auth Middleware!';
        $closure = function () use ($middlewareMockResponse) {
            return $middlewareMockResponse;
        };

        $middleware = new WPAuthMiddleware($this->authenticator);

        $middlewareResponse = $middleware->handle($request, $closure);

        $this->assertEquals($middlewareMockResponse, $middlewareResponse);
        // $this->assertEquals($userMock, WPAuthUser::getUser());
    }

    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     * @expectedExceptionMessage Invalid token.
     */
    public function testAuthTokenInvalid()
    {
        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9iaWMubG9jYWxob3N0IiwiaWF0IjoxNTI1ODcxNjY2LCJuYmYiOjE1MjU4NzE2NjYsImV4cCI6MTUyNjQ3NjQ2NiwiZGF0YSI6eyJ1c2VyIjp7ImlkIjoiMjIifX19.t_bhXIpvhd0uTa3XxUpYRgBRLUZdx1TQcsaIuoa_J-4';
        $request = Request::create(
            // uri (string)
            '/api/configurations',
            // method (string)
            'GET',
            // parameters (array)
            [],
            // cookies (array)
            [],
            // files (array)
            [],
            // server (array)
            ['HTTP_Authorization' => 'Bearer ' . $token],
            // content (array|string|resource|null)
            null
        );

        $closure = function () {
        };

        $this->authenticator
            ->shouldReceive('authenticate')
            ->with($token)
            ->andThrow(UnauthorizedHttpException::class, 'Bearer', 'Invalid token.')
            ->times(1);

        $middleware = new WPAuthMiddleware($this->authenticator);

        $response = $middleware->handle($request, $closure);
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     * @expectedExceptionMessage Authorization token not found.
     */
    public function testAuthFailNoHeader()
    {
        $request = Request::create('');
        $closure = function () {
        };

        $middleware = new WPAuthMiddleware($this->authenticator);

        $middleware->handle($request, $closure);
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     * @expectedExceptionMessage Authorization token not found.
     */
    public function testAuthFailMalformedHeader()
    {
        $request = Request::create(
            // uri (string)
            '/api/configurations',
            // method (string)
            'GET',
            // parameters (array)
            [],
            // cookies (array)
            [],
            // files (array)
            [],
            // server (array)
            ['HTTP_Authorization' => 'BrmBem eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9iaWMubG9jYWxob3N0IiwiaWF0IjoxNTI1ODcxNjY2LCJuYmYiOjE1MjU4NzE2NjYsImV4cCI6MTUyNjQ3NjQ2NiwiZGF0YSI6eyJ1c2VyIjp7ImlkIjoiMjIifX19.t_bhXIpvhd0uTa3XxUpYRgBRLUZdx1TQcsaIuoa_J-4'],
            // content (array|string|resource|null)
            null
        );
        
        $closure = function () {
        };

        $middleware = new WPAuthMiddleware($this->authenticator);

        $middleware->handle($request, $closure);
    }
}
