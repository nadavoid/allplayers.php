<?php

namespace AllPlayers\Command;

use Guzzle\Service\Command\AbstractCommand;

/**
 * Sends a request to post a message in notifier.
 *
 * @guzzle title type="string" required="true" doc="Title of notifier message."
 * @guzzle body  type="string" required="true" doc="Body of notifier message."
 */
class CreateNotifier extends AbstractCommand
{
    protected function build()
    {
        $params = $this->getAll(array('title', 'body'));
        $this->request = $this->client->post('notifier', NULL, $params);
    }
}
