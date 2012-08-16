<?php

namespace Guzzle\AllPlayers\Command;

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
        $client->execute($command);
        $this->user = json_decode($command->getResponse()->getBody());
        $user = $this->user;
    }

    public function testGetUser()
    {
        $client = $this->getServiceBuilder()->get('test.allplayers');
        $command = $client->getCommand('get_user', array('uuid' => $this->user->uuid));
        $client->execute($command);
        $response = json_decode($command->getResponse()->getBody());
        $this->assertEquals($response->uuid, $this->user->uuid);
    }
}