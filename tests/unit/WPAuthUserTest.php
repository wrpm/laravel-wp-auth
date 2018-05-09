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

    public function testSetUser()
    {
        $user = [
            'id' => 1,
            'name' => 'John'
        ];
        $this->testsub->setUser($user);
    }

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
}
