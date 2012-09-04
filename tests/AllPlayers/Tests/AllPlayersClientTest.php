<?php

namespace AllPlayers\Tests;

use AllPlayers\AllPlayersClient;
use Guzzle\Http\Client;
use Guzzle\Common\Log\ClosureLogAdapter;
use Guzzle\Http\Plugin\LogPlugin;

class AllPlayersClientTest extends \Guzzle\Tests\GuzzleTestCase
{
    /**
     * @var LogPlugin
     */
    private $plugin;

    /**
     * @var ClosureLogAdapter
     */
    private $logAdapter;
    
    // Test for creating an AllPlayers client.
    // Use phpunit.xml file to set _SERVER params for user and password.
    public function testBuilderCreatesClient()
    {
        $client = $this->getServiceBuilder()->get('test.allplayers');
        $this->assertInstanceOf('AllPlayers\AllPlayersClient', $client);
    }
}
