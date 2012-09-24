<?php

namespace AllPlayers\Command;

use Guzzle\Tests\GuzzleTestCase;
use AllPlayers\Tests\User\Fixtures\RandomUser;

class GetUserTest extends GuzzleTestCase
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
        $client = $this->getServiceBuilder()->get('test.allplayers');
        $random_user = new RandomUser();
        $command = $client->getCommand('CreateUser', (array) $random_user);
        $this->user = $client->execute($command);
    }

    public function testGetUser()
    {
        $client = $this->getServiceBuilder()->get('test.allplayers');
        $command = $client->getCommand('GetUser', array('uuid' => $this->user['uuid']));
        $user_retrieved = $client->execute($command);
        $this->assertEquals($user_retrieved, $this->user);
    }
}