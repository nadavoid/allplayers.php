<?php

namespace AllPlayers\Command;

use Guzzle\Tests\GuzzleTestCase;

class GetGroupsTest extends GuzzleTestCase
{
    public function testGetGroups()
    {
        $client = $this->getServiceBuilder()->get('test.allplayers');
        $command = $client->getCommand('get_groups');
        $groups = $client->execute($command);
        // @TODO: Actually do something, i.e. check that these groups belong to user.
    }
}
