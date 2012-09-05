<?php

namespace AllPlayers\Command;

use Guzzle\Tests\GuzzleTestCase;

class GetUserGroupsTest extends GuzzleTestCase
{

    public function testGetUserGroups()
    {
        $client = $this->getServiceBuilder()->get('test.allplayers');
        $command = $client->getCommand('get_user_groups');
        $client->execute($command);
        $response = json_decode($command->getResponse()->getBody());
    }
}