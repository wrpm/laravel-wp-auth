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


use Dingo\Api\Http\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use WRPM\LaravelWPAuth\Facades\WPAuthUser;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Config;



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
class JWTDecodeAuthenticator implements AuthenticatorContract
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
    public function __construct()
    {
        $this->secretKey = Config::get('wpauth.jwt_auth_secret_key');
        $this->issuer = Config::get('wpauth.jwt_issuer');
    }

    public function authenticate($token)
    {
        try {
            $decoded = JWT::decode($token, $this->secretKey, array('HS256'));
            $decoded = json_decode(json_encode($decoded), true);
        } catch (\Exception $e) {

            // Something is wrong trying to decode the token, send back the error
            throw new UnauthorizedHttpException('Bearer', 'Invalid token.');
        }

        // The Token is decoded now validate the iss
        if ($decoded['iss'] != $this->issuer) {

            // The iss do not match, throw error
            throw new UnauthorizedHttpException('Bearer', 'The iss do not match with this server.');
        }

        // So far so good, validate the user id in the token
        if (!isset($decoded['data']['user']['id'])) {

            // No user id in the token, throw error!!
            throw new UnauthorizedHttpException('Bearer', 'User ID not found in the token.');
        }

        // get user from response
        $userData = $decoded['data']['user'];

        // set user data for further use
        WPAuthUser::setUser($userData);
    }
}