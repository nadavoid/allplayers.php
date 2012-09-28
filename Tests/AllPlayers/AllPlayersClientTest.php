<?php
namespace AllPlayers\Tests;

use AllPlayers\AllPlayersClient;

class AllPlayersClientTest extends \Guzzle\Tests\GuzzleTestCase
{

    // Test for creating an AllPlayers client.
    // Use phpunit.xml file to set _SERVER params for user and password.
    public function testBuilderCreatesClient()
    {
        $client = $this->getServiceBuilder()->get('test.allplayers');
        $this->assertInstanceOf('AllPlayers\AllPlayersClient', $client);
    }
}
