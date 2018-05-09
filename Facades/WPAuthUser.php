<?php
/*
 * This file is part of the wprm/laravel-wp-auth package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WRPM\LaravelWPAuth\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * WPAuthUser class facade
 *
 * Service for authenticated user from WP
 *
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 */
class WPAuthUser extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'wp.auth.user';
    }
}
