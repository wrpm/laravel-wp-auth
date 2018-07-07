<?php
/*
 * This file is part of the wrpm/laravel-wp-auth package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


return [

    'jwt_auth_secret_key' => env('JWT_AUTH_SECRET_KEY', ''),

    'jwt_issuer' => env('JWT_AUTH_ISSUER', ''),

    'use_wp_api' => env('USE_WP_API', FALSE),

    'wp_url' => env('WP_URL', ''),

    'wp_timeout' => env('WP_TIMEOUT', 10),
    
    'accept_url_param_token' => env('WPAUTH_ACCEPT_URL_PARAM_TOKEN', false)
];
