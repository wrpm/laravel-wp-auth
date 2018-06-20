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
use WRPM\LaravelWPAuth\Authenticators\AuthenticatorContract;

class WPAuthMiddleware
{
    /**
     * The token authenticator implementation.
     *
     * @var WRPM\LaravelWPAuth\Authenticators\AuthenticatorContract
     */
    protected $authenticator;

    /**
     * Create a new auth instance.
     *
     * @param  WRPM\LaravelWPAuth\Authenticators\AuthenticatorContract  $client
     * @return void
     */
    public function __construct(AuthenticatorContract $authenticator)
    {
        $this->authenticator = $authenticator;
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

        $this->authenticator->authenticate($token);

        clock()->endEvent('wpauth-middleware-wrapper');

        // continue with process
        return $next($request);
    }
}
