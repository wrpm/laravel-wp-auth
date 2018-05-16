<?php

namespace WRPM\LaravelWPAuth\Http\Middleware;

use Closure;
use GuzzleHttp\Client;
use Dingo\Api\Http\Response;
use GuzzleHttp\Psr7\Request as ApiRequest;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use WRPM\LaravelWPAuth\Facades\WPAuthUser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class WPAuthMiddleware
{
    /**
     * The Guzzle Client implementation.
     *
     * @var GuzzleHttp\Client
     */
    protected $client;

    /**
     * Create a new auth instance.
     *
     * @param  GuzzleHttp\Client  $client
     * @return void
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        clock()->startEvent('wpauth-middleware-wrapper', "Authenticating user");


        // Look for the HTTP_AUTHORIZATION header
        $header = $request->header('Authorization', false);
        $token = false;
        if ($header) {
            list($token) = sscanf($header, 'Bearer %s');
        }

        if (!$token && Config::get('wpauth.accept_url_param_token')) {
            $token = $request->input('access_token', false);
        }

        if (!$token) {
            throw new UnauthorizedHttpException('Bearer', 'Authorization token not found.');
        }

        // try to authenticate with wordpress
        try {
            $response = $this->requestUserByToken($token);
        } catch (RequestException $e) {
            
            // get request
            $req = $e->getRequest();
            $reqBody = (string)$req->getBody();

            // get response
            $res = $e->getResponse();
            if (!$res) {
                Log::error($e);
                return new Response(['message' => 'Can\'t connect to WP Server'], 500);
            }
            $status = $res->getStatusCode();
            $body = $res->getBody();
            $data = json_decode($body, true);

            // return response error
            return new Response($data, $status);
        }

        // get user from response
        $body = $response->getBody();
        $userData = json_decode($body, true);

        // set user data for further use
        WPAuthUser::setUser($userData);

        clock()->endEvent('wpauth-middleware-wrapper');

        // continue with process
        return $next($request);
    }

    public function requestUserByToken($token)
    {
        return $this->client->request(
            'GET',
            'wp-json/wp/v2/users/me?context=edit',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token
                ]
            ]
        );
    }
}
