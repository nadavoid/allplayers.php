<?php

namespace AllPlayers\Command;

use Guzzle\Tests\GuzzleTestCase;

class LogoutUserTest extends GuzzleTestCase
{
    public function testLogoutUser()
    {
        $client = $this->getServiceBuilder()->get('admin.cookies');
        $command = $client->getCommand('logout_user');
        $response = $client->execute($command);
        $this->assertTrue($response);
    }
}
