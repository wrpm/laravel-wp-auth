<?php

use WRPM\LaravelWPAuth\Http\Middleware\WPAuthMiddleware;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler as GuzzleMockHandler;
use GuzzleHttp\HandlerStack as GuzzleHandlerStack;
use Illuminate\Http\Request;
use Illuminate\Support\Debug\Dumper;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Middleware as GuzzleMiddleware;
use GuzzleHttp\Exception\RequestException;
use WRPM\LaravelWPAuth\Facades\WPAuthUser;

class WPAuthMiddlewareTest extends Orchestra\Testbench\TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->container = [];
        $this->history = GuzzleMiddleware::history($this->container);

        $this->guzzleMockHandler = new GuzzleMockHandler();
        $this->guzzleHandler = GuzzleHandlerStack::create($this->guzzleMockHandler);
        $this->guzzleHandler->push($this->history);
        $this->guzzleClient = new GuzzleClient(['handler' => $this->guzzleHandler]);
        $this->request = Request::create(
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
            ['HTTP_Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9iaWMubG9jYWxob3N0IiwiaWF0IjoxNTI1ODcxNjY2LCJuYmYiOjE1MjU4NzE2NjYsImV4cCI6MTUyNjQ3NjQ2NiwiZGF0YSI6eyJ1c2VyIjp7ImlkIjoiMjIifX19.t_bhXIpvhd0uTa3XxUpYRgBRLUZdx1TQcsaIuoa_J-4'],
            // content (array|string|resource|null)
            null
        );
        $this->d = new Dumper();
    }

    public function tearDown()
    {
        unset($this->guzzleClient);
        unset($this->guzzleMockHandler);
        unset($this->guzzleHandler);
        unset($this->request);
        unset($this->container);
        unset($this->history);

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
    public function testMakeRequest()
    {
        $status = 200;
        $headers = ['X-Foo' => 'Bar'];
        $body = 'hello!';
        $protocol = '1.1';
        $mockResponse = new GuzzleResponse($status, $headers, $body, $protocol);
        $this->guzzleMockHandler->append($mockResponse);

        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9iaWMubG9jYWxob3N0IiwiaWF0IjoxNTI1ODcxNjY2LCJuYmYiOjE1MjU4NzE2NjYsImV4cCI6MTUyNjQ3NjQ2NiwiZGF0YSI6eyJ1c2VyIjp7ImlkIjoiMjIifX19.t_bhXIpvhd0uTa3XxUpYRgBRLUZdx1TQcsaIuoa_J-4';
        
        $middleware = new WPAuthMiddleware($this->guzzleClient);

        $response = $middleware->requestUserByToken($token);

        $this->assertEquals($mockResponse, $response);

        $this->assertEquals(1, count($this->container));

        $transaction = $this->container[0];

        $this->assertEquals('GET', $transaction['request']->getMethod());
        $this->assertTrue($transaction['request']->hasHeader('Authorization'));
        $authorizationHeader = $transaction['request']->getHeader('Authorization');
        $this->assertEquals('Bearer ' . $token, $authorizationHeader[0]);
        $uri = $transaction['request']->getUri();
        $this->assertEquals('wp-json/wp/v2/users/me', $uri->getPath());
        $this->assertEquals('context=edit', $uri->getQuery());
    }

    /**
     * @test
     */
    public function testAuthSuccess()
    {
        $status = 200;
        $headers = [
            'Access-Control-Allow-Headers' => ['Authorization', 'Content-Type'],
            'Access-Control-Allow-Origin' => '*',
            'Allow' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
            'Cache-Control' => ['no-cache', 'must-revalidate', 'max-age=0'],
            'Connection' => 'keep-alive',
            'Content-Type' => 'application/json; charset=UTF-8',
        ];

        $userJsonMock = '{"id":22,"username":"nikolaplavsic","name":"nikolaplavsic","first_name":"Nikola","last_name":"Plavsic","email":"nikolaplavsic@gmail.com","url":"","description":"","link":"http:\/\/bic.localhost\/author\/nikolaplavsic\/","locale":"en_US","nickname":"nikolaplavsic","slug":"nikolaplavsic","roles":["administrator"],"registered_date":"2013-10-02T07:47:44+00:00","capabilities":{"switch_themes":true,"edit_themes":true,"activate_plugins":true,"edit_plugins":true,"edit_users":true,"edit_files":true,"manage_options":true,"moderate_comments":true,"manage_categories":true,"manage_links":true,"upload_files":true,"import":true,"unfiltered_html":true,"edit_posts":true,"edit_others_posts":true,"edit_published_posts":true,"publish_posts":true,"edit_pages":true,"read":true,"level_10":true,"level_9":true,"level_8":true,"level_7":true,"level_6":true,"level_5":true,"level_4":true,"level_3":true,"level_2":true,"level_1":true,"level_0":true,"edit_others_pages":true,"edit_published_pages":true,"publish_pages":true,"delete_pages":true,"delete_others_pages":true,"delete_published_pages":true,"delete_posts":true,"delete_others_posts":true,"delete_published_posts":true,"delete_private_posts":true,"edit_private_posts":true,"read_private_posts":true,"delete_private_pages":true,"edit_private_pages":true,"read_private_pages":true,"delete_users":true,"create_users":true,"unfiltered_upload":true,"edit_dashboard":true,"update_plugins":true,"delete_plugins":true,"install_plugins":true,"update_themes":true,"install_themes":true,"update_core":true,"list_users":true,"remove_users":true,"promote_users":true,"edit_theme_options":true,"delete_themes":true,"export":true,"manage_woocommerce":true,"view_woocommerce_reports":true,"edit_product":true,"read_product":true,"delete_product":true,"edit_products":true,"edit_others_products":true,"publish_products":true,"read_private_products":true,"delete_products":true,"delete_private_products":true,"delete_published_products":true,"delete_others_products":true,"edit_private_products":true,"edit_published_products":true,"manage_product_terms":true,"edit_product_terms":true,"delete_product_terms":true,"assign_product_terms":true,"edit_shop_order":true,"read_shop_order":true,"delete_shop_order":true,"edit_shop_orders":true,"edit_others_shop_orders":true,"publish_shop_orders":true,"read_private_shop_orders":true,"delete_shop_orders":true,"delete_private_shop_orders":true,"delete_published_shop_orders":true,"delete_others_shop_orders":true,"edit_private_shop_orders":true,"edit_published_shop_orders":true,"manage_shop_order_terms":true,"edit_shop_order_terms":true,"delete_shop_order_terms":true,"assign_shop_order_terms":true,"edit_shop_coupon":true,"read_shop_coupon":true,"delete_shop_coupon":true,"edit_shop_coupons":true,"edit_others_shop_coupons":true,"publish_shop_coupons":true,"read_private_shop_coupons":true,"delete_shop_coupons":true,"delete_private_shop_coupons":true,"delete_published_shop_coupons":true,"delete_others_shop_coupons":true,"edit_private_shop_coupons":true,"edit_published_shop_coupons":true,"manage_shop_coupon_terms":true,"edit_shop_coupon_terms":true,"delete_shop_coupon_terms":true,"assign_shop_coupon_terms":true,"edit_fancy_product_desiger":true,"wpseo_bulk_edit":true,"edit_shop_webhook":true,"read_shop_webhook":true,"delete_shop_webhook":true,"edit_shop_webhooks":true,"edit_others_shop_webhooks":true,"publish_shop_webhooks":true,"read_private_shop_webhooks":true,"delete_shop_webhooks":true,"delete_private_shop_webhooks":true,"delete_published_shop_webhooks":true,"delete_others_shop_webhooks":true,"edit_private_shop_webhooks":true,"edit_published_shop_webhooks":true,"manage_shop_webhook_terms":true,"edit_shop_webhook_terms":true,"delete_shop_webhook_terms":true,"assign_shop_webhook_terms":true,"wpseo_manage_options":true,"administrator":true},"extra_capabilities":{"administrator":true},"avatar_urls":{"24":"http:\/\/1.gravatar.com\/avatar\/d53f1582fac589cd33a13decac9632fa?s=24&d=mm&r=g","48":"http:\/\/1.gravatar.com\/avatar\/d53f1582fac589cd33a13decac9632fa?s=48&d=mm&r=g","96":"http:\/\/1.gravatar.com\/avatar\/d53f1582fac589cd33a13decac9632fa?s=96&d=mm&r=g"},"meta":[],"_links":{"self":[{"href":"http:\/\/bic.localhost\/wp-json\/wp\/v2\/users\/22"}],"collection":[{"href":"http:\/\/bic.localhost\/wp-json\/wp\/v2\/users"}]}}';
        $body = $userJsonMock;
        $userMock = json_decode($userJsonMock, true);
        $protocol = '1.1';
        $response = new GuzzleResponse($status, $headers, $body, $protocol);
        $this->guzzleMockHandler->append($response);

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
            ['HTTP_Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9iaWMubG9jYWxob3N0IiwiaWF0IjoxNTI1ODcxNjY2LCJuYmYiOjE1MjU4NzE2NjYsImV4cCI6MTUyNjQ3NjQ2NiwiZGF0YSI6eyJ1c2VyIjp7ImlkIjoiMjIifX19.t_bhXIpvhd0uTa3XxUpYRgBRLUZdx1TQcsaIuoa_J-4'],
            // content (array|string|resource|null)
            null
        );
        $middlewareMockResponse = 'Hello from WP Auth Middleware!';
        $closure = function () use ($middlewareMockResponse) {
            return $middlewareMockResponse;
        };
        $middleware = new WPAuthMiddleware($this->guzzleClient);

        $middlewareResponse = $middleware->handle($request, $closure);

        $this->assertEquals($middlewareMockResponse, $middlewareResponse);
        $this->assertEquals($userMock, WPAuthUser::getUser());
    }

    /**
     * @test
     */
    public function testAuthTokenInvalid()
    {
        $status = 403;
        $headers = [
            'Access-Control-Allow-Headers' => ['Authorization', 'Content-Type'],
            'Allow' => ['GET'],
            'Cache-Control' => ['no-cache', 'must-revalidate', 'max-age=0'],
            'Connection' => 'keep-alive',
            'Content-Type' => 'application/json; charset=UTF-8',
        ];

        $body = '{"code":"jwt_auth_invalid_token","message":"Syntax error, malformed JSON","data":{"status":403}}';
        $protocol = '1.1';
        $response = new GuzzleResponse($status, $headers, $body, $protocol);
        $requestException = new RequestException('Syntax error, malformed JSON', new GuzzleRequest('GET', 'api'), $response);
        $this->guzzleMockHandler->append($requestException);
        
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
            ['HTTP_Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9iaWMubG9jYWxob3N0IiwiaWF0IjoxNTI1ODcxNjY2LCJuYmYiOjE1MjU4NzE2NjYsImV4cCI6MTUyNjQ3NjQ2NiwiZGF0YSI6eyJ1c2VyIjp7ImlkIjoiMjIifX19.t_bhXIpvhd0uTa3XxUpYRgBRLUZdx1TQcsaIuoa_J-4'],
            // content (array|string|resource|null)
            null
        );

        $closure = function () {
        };
        $middleware = new WPAuthMiddleware($this->guzzleClient);

        $response = $middleware->handle($request, $closure);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $responseContent = $response->getContent();
        $responseContentData = json_decode($responseContent, true);
        $this->assertEquals('jwt_auth_invalid_token', $responseContentData['code']);
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
        $middleware = new WPAuthMiddleware($this->guzzleClient);

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
        $middleware = new WPAuthMiddleware($this->guzzleClient);

        $middleware->handle($request, $closure);
    }
}
