<?php

namespace AllPlayers\Command;
use Guzzle\Service\Command\AbstractCommand;

/**
 * Sends a simple API request to an example web service
 *
 * @guzzle uuid doc="Destination object user" required="true"
 * @guzzle headers doc="Headers to set on the request" type="class:Guzzle\Common\Collection"
 */
class GetUser extends AbstractCommand
{
    /**
     * Set the destination uuid
     *
     * @param string $uuid Destination uuid that will be added to the path
     *
     * @return GetUser
     */
    public function setUuid($uuid)
    {
        return $this->set('uuid', $uuid);
    }

    protected function build()
    {
        $this->request = $this->client->get(array('users/{uuid}', $this->data));
    }
}
