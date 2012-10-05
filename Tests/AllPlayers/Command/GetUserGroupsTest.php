<?php

namespace AllPlayers\Command;

use Guzzle\Tests\GuzzleTestCase;

class GetUserGroupsTest extends GuzzleTestCase
{

    public function testGetUserGroups()
    {
        $client = $this->getServiceBuilder()->get('admin.basic');
        $command = $client->getCommand('GetUserGroups');
        $groups = $client->getIterator($command);
        foreach ($groups as $group)
        {
            if (is_array($group))
            {
                // TODO: Actually check if group is an object. Additionally,
                // assume that logged in user has groups associated.
                assert(is_string($group['uuid']));
            }
        }
    }
}