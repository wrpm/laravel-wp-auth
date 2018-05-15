<?php

use WRPM\LaravelWPAuth\Services\WPAuthUser;

class WPAuthUserTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->testsub = new WPAuthUser();
    }

    public function tearDown()
    {
        unset($this->testsub);
    }

    /**
     * @test
     */
    public function testSetUser()
    {
        $user = [
            'id' => 1,
            'name' => 'John'
        ];

        $this->testsub->setUser($user);
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     * @expectedExceptionMessage Invalid user.
     */
    public function testSetUserWithoutID()
    {
        $user = [
            'name' => 'John'
        ];

        $this->testsub->setUser($user);
    }

    /**
     * @test
     */
    public function testGetUser()
    {
        $user = [
            'id' => 1,
            'name' => 'John'
        ];
        $this->testsub->setUser($user);


        $retreivedUser = $this->testsub->getUser();

        $this->assertEquals($user, $retreivedUser);
    }

    /**
     * @test
     */
    public function testGetUserID()
    {
        $user = [
            'id' => 1,
            'name' => 'John'
        ];
        $this->testsub->setUser($user);

        $retreivedUserId = $this->testsub->getUserId();

        $this->assertEquals($user['id'], $retreivedUserId);
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     * @expectedExceptionMessage Not Authorized.
     */
    public function testGetUserWhenNoUserSetted()
    {
        $retreivedUser = $this->testsub->getUser();
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     * @expectedExceptionMessage Not Authorized.
     */
    public function testGetUserIdWhenNoUserSetted()
    {
        $retreivedUserId = $this->testsub->getUserId();
    }

    /**
     * @test
     */
    public function testCheckIfUserIsAuthenticated()
    {
        $authenticated = $this->testsub->check();

        $this->assertFalse($authenticated);

        $user = [
            'id' => 1,
            'name' => 'John'
        ];
        $this->testsub->setUser($user);

        $authenticated = $this->testsub->check();

        $this->assertTrue($authenticated);
    }

    /**
     * @test
     */
    public function testCheckIfUserHasRole()
    {
        $user = [
            'id' => 1,
            'name' => 'John',
            'roles' => [
                'administrator',
                'editor'
            ]
        ];
        $this->testsub->setUser($user);

        $userHasRole = $this->testsub->is('administrator');

        $this->assertTrue($userHasRole);

        $userHasRole = $this->testsub->is('developer');

        $this->assertFalse($userHasRole);
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     * @expectedExceptionMessage Not Authorized.
     */
    public function testCheckIfUserHasRoleWhenNoUserSetted()
    {
        $userHasRole = $this->testsub->is('administrator');
    }

    /**
     * @test
     */
    public function testCheckIfUserHasCapability()
    {
        $user = [
            'id' => 1,
            'name' => 'John',
            'capabilities' => [
                'edit_post' => true,
                'edit_user' => false,
            ]
        ];
        $this->testsub->setUser($user);

        $userHasCapability = $this->testsub->can('edit_post');

        $this->assertTrue($userHasCapability);

        $userHasCapability = $this->testsub->can('edit_user');

        $this->assertFalse($userHasCapability);

        $userHasCapability = $this->testsub->can('edit_api');

        $this->assertFalse($userHasCapability);
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     * @expectedExceptionMessage Not Authorized.
     */
    public function testCheckIfUserHasCapabilityWhenNoUserSetted()
    {
        $userHasRole = $this->testsub->can('edit_post');
    }
}
