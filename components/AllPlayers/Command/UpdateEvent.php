<?php

namespace AllPlayers\Command;
use Guzzle\Service\Command\AbstractCommand;

/**
 * Sends an API request to update an event.
 *
 * @guzzle groups doc="Groups(uuids) involved in event." type="array"
 * @guzzle title doc="Title of event." type="string"
 * @guzzle description doc="Description of event." type="string"
 * @guzzle date_time doc="Date and time of start and end of event, e.g. date_time[start] 2012-03-30T19:54:00." type="array"
 * @guzzle category doc="Category of the event, e.g. game." type="string"
 * @guzzle resources doc="Resources(uuids) where this event takes place." type="array"
 * @guzzle competitors doc="Associative array of competitor gid : competitor label : competitor score." type="array"
 * @guzzle published doc="Published status, e.g. TRUE." type="string"
 * @guzzle external_id doc="External ID up to 72 characters to be used for relating to external content." type="string"
 */
class UpdateEvent extends AbstractCommand
{
    /**
     * @param array $params Settings for members retrieval.
     */
    protected $uuid = NULL;

    /**
     * Set the update event params.
     *
     * @param array $params
     *  Params array containing all fields.
     *
     * @return All params array.
     */
    public function setParams($params)
    {
        foreach ($params as $key => $value) {
            $this->setParams($params);
        }

        return $this->getAll($params);
    }

    /**
     * Set the event UUID for the event you're gonna update.
     *
     * @param string $uuid
     *
     * @return UpdateEvent
     */
    public function setUuid($uuid) {
        $this->uuid = $uuid;
    }

    protected function build()
    {
        $params = $this->getAll(array(
            'groups', 'title', 'description', 'date_time', 'category',
            'resources', 'competitors', 'published', 'external_id'));
        $this->request = $this->client->put(array('events/{uuid}', array('uuid' => $this->uuid)));
        $this->request->setBody(json_encode($params), 'application/json');
    }
}
