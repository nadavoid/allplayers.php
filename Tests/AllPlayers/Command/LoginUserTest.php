<?php

namespace AllPlayers\Command;

use Guzzle\Tests\GuzzleTestCase;
use AllPlayers\Tests\User\Fixtures\RandomUser;

class LoginUserTest extends GuzzleTestCase
{
    /**
     * @var object User
     */
    private $user;

    /**
     * Setting up get user test by creating the user first.
     */
    public function setUp()
    {
        $client = $this->getServiceBuilder()->get('admin.basic');
        $random_user = new RandomUser();
        $command = $client->getCommand('create_user', (array) $random_user);
        $this->user = $client->execute($command);
        $this->user['password'] = $random_user->password;
    }

    public function testLoginUser()
    {
        $client = $this->getServiceBuilder()->get('anonymous.allplayers');
        $command = $client->getCommand('login_user', array('email' => $this->user['email'], 'password' => $this->user['password']));
        $user = $client->execute($command);
        $this->assertEquals($user['user']['firstname'], $this->user['firstname']);
        $this->assertEquals($user['user']['lastname'], $this->user['lastname']);
        $this->assertEquals($user['user']['email'], $this->user['email']);
        $this->assertEquals($user['user']['gender'], $this->user['gender']);
    }
}
