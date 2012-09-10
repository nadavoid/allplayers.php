<?php

namespace AllPlayers\Model;

use Guzzle\Service\Resource\ResourceIterator;

/**
 * Iterate over a GetUserGroups command
 */
class GetUserGroupsIterator extends ResourceIterator
{
    protected function sendRequest()
    {
        $limit = $this->command->get('limit') ? $this->command->get('limit') : 10;
        // If a next token is set, then add it to the command
        if ($this->nextToken)
        {
            $this->command->setOffset($this->retrievedCount);
        }

        // Execute the command and parse the result
        $result = $this->command->execute();

        $this->nextToken = sizeof($result) == $limit ? TRUE : FALSE;
        return $result;
    }
}
