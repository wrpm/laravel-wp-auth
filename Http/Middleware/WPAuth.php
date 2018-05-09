<?php

namespace WRPM\LaravelWPAuth\Http\Middleware;

use Closure;
use GuzzleHttp\Client;
use Dingo\Api\Http\Response;
use GuzzleHttp\Psr7\Request as ApiRequest;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use WRPM\LaravelWPAuth\Facades\WPAuthUser;

class WPAuth
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

        // Look for the HTTP_AUTHORIZATION header
        $header = $request->header('Authorization', false);

        if (!$header) {
            throw new UnauthorizedHttpException('Bearer', 'Authorization header not found.');
        }

        /*
         * The Authorization is present verify the format
         * if the format is wrong throw exception
         */
        list($token) = sscanf($header, 'Bearer %s');
        if (!$token) {
            throw new UnauthorizedHttpException('Bearer', 'Authorization header malformed.');
        }

        // try to authenticate with wordpress
        try {
            $response = $this->client->request(
                'GET',
                'wp-json/wp/v2/users/me?context=edit',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token
                    ]
                ]
            );

            // get user from response
            $body = $response->getBody();
            $userData = json_decode($body, true);

            // set user data for further use
            WPAuthUser::setUser($userData);

            // continue with process
            return $next($request);
        } catch (RequestException $e) {
            
            // get request
            $req = $e->getRequest();
            $reqBody = (string)$req->getBody();

            // get response
            $res = $e->getResponse();
            $status = $res->getStatusCode();
            $body = $res->getBody();
            $data = json_decode($body, true);

            // return response error
            return new Response($data, $status);
        }
    }
}
