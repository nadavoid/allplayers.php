<?php

namespace AllPlayers\Command;

use Guzzle\Service\Command\AbstractCommand;

/**
 * Sends a request to get a User using uuid.
 *
 * @guzzle uuid    type="string" required="true" doc="Destination object user"
 */
class GetUser extends AbstractCommand
{
    protected function build()
    {
        $this->request = $this->client->get(array(
            'users/{uuid}', array(
                'uuid' => ($this->get('uuid')) ? $this->get('uuid') : 'current'
            )
        ));
    }
}
