<?php

namespace AllPlayers\Command;

use Guzzle\Service\Command\AbstractCommand;

/**
 * Gets all the user's (specified or current) groups providing a UUID.
 *
 * @guzzle uuid   type="string" doc="The uuid of the user to retrieve groups for."
 * @guzzle offset type="integer" doc="Set offset on groups on starting point to retrieve groups."
 * @guzzle limit  type="integer" doc="Set limit on how many groups should be returned."
 */
class GetUserGroups extends AbstractCommand
{
    protected function build()
    {
        $params = $this->getAll(array('offset', 'limit'));
        $this->request = $this->client->get(array(
            'users/{uuid}/groups{?params*}', array(
                'uuid' => ($this->get('uuid')) ? $this->get('uuid') : 'current',
                'params' => $params
            )
        ));
    }
}
