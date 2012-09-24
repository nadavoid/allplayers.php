<?php

namespace AllPlayers\Command;

use Guzzle\Service\Command\AbstractCommand;

/**
 * Get all the members in a group.
 *
 * @guzzle uuid        type="string" required="true" doc="The uuid of the group to retrieve members for."
 * @guzzle limit       type="integer" doc="Set limit on how many members should be retrieved."
 * @guzzle admins_only type="boolean" doc="TRUE or FALSE, to return admins or not."
 */
class GetGroupMembers extends AbstractCommand
{
    protected function build()
    {
        $params = $this->getAll(array('admins_only', 'limit'));
        $this->request = $this->client->get(array(
            'groups/{uuid}/members{?params*}', array(
                'uuid' => $this->get('uuid'),
                'params' => $params
            )
        ));
    }
}
