<?php

namespace Guzzle\AllPlayers\Command;

use Guzzle\Tests\GuzzleTestCase;

class GetGroupsTest extends GuzzleTestCase
{

    public function testGetGroups()
    {
        $client = $this->getServiceBuilder()->get('test.allplayers');
        $command = $client->getCommand('get_groups');
        $client->execute($command);
        $response = json_decode($command->getResponse()->getBody());
    }
}