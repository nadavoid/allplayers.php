<?php

namespace AllPlayers\Command;
use Guzzle\Service\Command\AbstractCommand;

/**
 * Gets all the user's (specified or current) groups.
 *
 * @guzzle uuid doc="The uuid of the user to retrieve groups for"
 * @guzzle headers doc="Headers to set on the request" type="class:Guzzle\Common\Collection"
 */
class GetUserGroups extends AbstractCommand
{
    /**
     * @param array $params Settings for groups retrieval.
     */
    protected $params = array();

    /**
     * Set limit on how many groups should be returned.
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
     * Set offset on groups on starting point to retrieve groups.
     *
     * @param int $offset
     *
     * @return GetUserGrups.
     */
    public function setOffset($offset)
    {
        $this->params = array('offset' => $offset);
        return $this;
    }

    /**
     * Set the uuid to retrieve groups from.
     *
     * @param string $uuid
     *
     * @return GetUserGroups
     */
    public function setUuid($uuid)
    {
        return $this->set('uuid', $uuid);
    }

    protected function build()
    {
        $this->request = $this->client->get(array('users/{uuid}/groups{?params*}', array(
            'uuid' => ($this->get('uuid')) ? $this->get('uuid') : 'current',
            'params' => $this->params
        )));
    }
}
