<?php
/*
 * This file is part of the wprm/laravel-wp-auth package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WRPM\LaravelWPAuth\Services;

// use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * WPAuthUser class
 *
 * Service for authenticated user from WP
 *
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 */
class WPAuthUser
{

    /**
     * User data
     *
     * @var array
     */
    protected $user = false;

    /**
     * Set WP User data
     *
     * @param array $user
     * @return void
     * @throws UnauthorizedHttpException
     */
    public function setUser(array $user)
    {
        if (!array_key_exists('id', $user) || empty($user['id'])) {
            throw new UnauthorizedHttpException('Bearer', 'Invalid user.');
        }
        $this->user = $user;
    }

    /**
     * Get authenticated user
     *
     * @return array user
     * @throws UnauthorizedHttpException
     */
    public function getUser()
    {
        if (!$this->user) {
            throw new UnauthorizedHttpException('Bearer', 'Not Authorized.');
        }

        return $this->user;
    }

    /**
     * Get authenticated user's ID
     *
     * @return integer ID
     * @throws UnauthorizedHttpException
     */
    public function getUserId()
    {
        if (!$this->user) {
            throw new UnauthorizedHttpException('Bearer', 'Not Authorized.');
        }

        return $this->user['id'];
    }

    /**
     * Check if user is authenticated
     *
     * @return boolean
     */
    public function check()
    {
        if (!$this->user) {
            return false;
        }

        return true;
    }

    /**
     * Check if user has a role
     *
     * @param string $role
     *
     * @return boolean Yes/No
     * @throws UnauthorizedHttpException
     */
    public function is($role)
    {
        if (!$this->user) {
            throw new UnauthorizedHttpException('Bearer', 'Not Authorized.');
        }

        if (
            empty($this->user['roles'])
            || !is_array($this->user['roles'])
            || !in_array($role, $this->user['roles'])
        ) {
            return false;
        }

        return true;
    }

    /**
     * Check if user have a capability
     *
     * @param string $capability
     *
     * @return boolean Yes/No
     * @throws UnauthorizedHttpException
     */
    public function can($capability)
    {
        if (!$this->user) {
            throw new UnauthorizedHttpException('Bearer', 'Not Authorized.');
        }

        if (
            empty($this->user['capabilities'])
            || !is_array($this->user['capabilities'])
            || !array_key_exists($capability, $this->user['capabilities'])
            || !$this->user['capabilities'][$capability]
        ) {
            return false;
        }

        return true;
    }
}
