<?php

namespace Guzzle\AllPlayers\Command;
use Guzzle\Service\Command\AbstractCommand;

/**
 * Sends a request to post a message in notifier.
 *
 * @guzzle title doc="Destination object user" required="true" type="string"
 * @guzzle body doc="body" required="true" type="string"
 */
class CreateNotifier extends AbstractCommand
{
    /**
     * Set the title
     *
     * @param string $title Title
     *
     * @return CreateNotifier
     */
    public function setTitle($title)
    {
        return $this->set('title', $title);
    }

    /**
     * Set the body
     *
     * @param string $body Body
     *
     * @return CreateNotifier
     */
    public function setBody($body)
    {
        return $this->set('body', $body);
    }

    protected function build()
    {
        $this->request = $this->client->post('notifier.json', NULL, $this->data);
    }
}
