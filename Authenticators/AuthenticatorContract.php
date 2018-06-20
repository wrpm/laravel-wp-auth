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

/**
 * Authenticator contract for wrpm/laravel-wp-auth package
 *
 * Authenticates the received token
 *
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	wrpm/laravel-wp-auth
 * @since 		1.0
 * @version  	1.0
 */
interface AuthenticatorContract
{

    
    public function authenticate($token);
}