<?php
/*
 * This file is part of the wrpm/laravel-wp-auth package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WRPM\LaravelWPAuth;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use WRPM\LaravelWPAuth\Authenticators\WPApiAuthenticator;
use WRPM\LaravelWPAuth\Authenticators\JWTDecodeAuthenticator;

/**
 * LaravelWPAuthServiceProvider service provider for wrpm/laravel-wp-auth package
 *
 * It will register classes and dependencies to app container
 *
 * @uses   		Illuminate\Support\ServiceProvider
 *
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	wrpm/laravel-wp-auth
 * @since 		1.0
 * @version  	1.0
 */
class LaravelWPAuthServiceProvider extends ServiceProvider
{

    /**
     * Register
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(realpath(__DIR__ . '/config/config.php'), 'wpauth');
        $this->registerDependencies();
    }

    /**
     * Boot
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/config.php' => config_path('wpauth.php'),
            // __DIR__ . '/database/migrations' => database_path('/migrations'),
        ]);
    }

    /**
     * Register dependencies for this package
     *
     * @return void
     */
    protected function registerDependencies()
    {
        $this->app->bind('WRPM\LaravelWPAuth\Http\Middleware\WPAuthMiddleware', function ($app) {

            $useWPApi = Config::get('wpauth.use_wp_api');

            if ($useWPApi) {
                $url = Config::get('wpauth.wp_url');
                $timeout = Config::get('wpauth.wp_timeout');
                $authenticator = new WPApiAuthenticator(
                    new \GuzzleHttp\Client([
                        // Base URI is used with relative requests
                        'base_uri' => $url,
                        // You can set any number of default request options.
                        'timeout' => $timeout
                    ])
                );
            } else {
                $url = Config::get('wpauth.wp_url');
                $timeout = Config::get('wpauth.wp_timeout');

                $authenticator = new JWTDecodeAuthenticator();
            }

            return new \WRPM\LaravelWPAuth\Http\Middleware\WPAuthMiddleware(
                $authenticator
            );
        });

        $this->app->singleton('wp.auth.user', function ($app) {
            return new \WRPM\LaravelWPAuth\Services\WPAuthUser();
        });
    }
}
