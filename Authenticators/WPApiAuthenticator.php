<?php
/*
 * This file is part of the wrpm/laravel-wp-auth package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WRPM\LaravelWPAuth\Authenticators;


use GuzzleHttp\Client;
use Dingo\Api\Http\Response;
use GuzzleHttp\Psr7\Request as ApiRequest;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use WRPM\LaravelWPAuth\Facades\WPAuthUser;
use Illuminate\Support\Facades\Log;


/**
 * WPApi authenticator for wrpm/laravel-wp-auth package
 *
 * It will check with WP API if token is valid
 *
 * @uses   		Illuminate\Support\ServiceProvider
 *
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	wrpm/laravel-wp-auth
 * @since 		1.0
 * @version  	1.0
 */
class WPApiAuthenticator implements AuthenticatorContract
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

    public function authenticate($token)
    {
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
                throw new UnauthorizedHttpException('Bearer', 'Can\'t connect to WP Server');
            }

            throw new UnauthorizedHttpException('Bearer', 'Invalid token.');
        }

        // get user from response
        $body = $response->getBody();
        $userData = json_decode($body, true);

        // set user data for further use
        WPAuthUser::setUser($userData);    
    }

    /**
     * Get user by token with guzzle client
     * from WP API
     * 
     * @param string $token
     * 
     * @return array
     */
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