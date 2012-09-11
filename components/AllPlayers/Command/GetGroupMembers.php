<?php

namespace AllPlayers\Command;
use Guzzle\Service\Command\AbstractCommand;

/**
 * Get all the members in a group.
 *
 * @guzzle uuid doc="The uuid of the group to retrieve members for" required="true"
 * @guzzle headers doc="Headers to set on the request" type="class:Guzzle\Common\Collection"
 */
class GetGroupMembers extends AbstractCommand
{
    /**
     * @param array $params Settings for members retrieval.
     */
    protected $params = array();

    /**
     * Set limit on how many members should be retrieved.
     *
     * @param int $limits
     *
     * @return GetUserGrups.
     */
    public function setLimit($limit)
    {
        $this->params = array('limit' => $limit);
        return $this->set('limit', $limit);
    }

    /**
     * Set the uuid to retrieve members from.
     *
     * @param string $uuid
     *
     * @return GetGroupMembers
     */
    public function setUuid($uuid)
    {
        return $this->set('uuid', $uuid);
    }

    protected function build()
    {
        $this->request = $this->client->get(array('groups/{uuid}/members{?params*}', array(
            'uuid' => $this->get('uuid'),
            'params' => $this->params
        )));
    }
}