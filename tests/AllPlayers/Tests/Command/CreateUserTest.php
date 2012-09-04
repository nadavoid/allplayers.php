<?php

namespace AllPlayers\Command;

use Guzzle\Tests\GuzzleTestCase;
use AllPlayers\Tests\User\Fixtures\RandomUser;

class CreateUserTest extends GuzzleTestCase
{
    public function testCreateUser()
    {
        $client = $this->getServiceBuilder()->get('test.allplayers');
        $random_user = new RandomUser();
        $command = $client->getCommand('CreateUser', (array) $random_user);
        $client->execute($command);
        $this->user = json_decode($command->getResponse()->getBody());
        $user = $this->user;
        $this->assertEquals($user->firstname, $random_user->firstname);
        $this->assertEquals($user->lastname, $random_user->lastname);
        $this->assertEquals($user->email, $random_user->email);
        $this->assertEquals(substr($user->gender, 0, 1), $random_user->gender);
    }
}
