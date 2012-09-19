<?php

namespace AllPlayers\Command;
use Guzzle\Service\Command\AbstractCommand;

/**
 * Sends an API request to create an event.
 *
 * @guzzle groups doc="Groups(uuids) involved in event." type="array" required="true"
 * @guzzle title doc="Title of event." type="string" required="true"
 * @guzzle description doc="Description of event." type="string" required="true"
 * @guzzle date_time doc="Date and time of start and end of event, e.g. date_time[start] 2012-03-30T19:54:00." type="array" required="true"
 * @guzzle category doc="Category of the event, e.g. game." type="string"
 * @guzzle resources doc="Resources(uuids) where this event takes place." type="array"
 * @guzzle competitors doc="Associative array of competitor gid : competitor label : competitor score." type="array"
 * @guzzle published doc="Published status, e.g. TRUE." type="string"
 * @guzzle external_id doc="External ID up to 72 characters to be used for relating to external content."
 */
class CreateEvent extends AbstractCommand
{
    /**
     * Set the create event params.
     *
     * @param array $params
     *  Params array containing all required fields.
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

    protected function build()
    {
        $this->request = $this->client->post('events', NULL, $this->data);
    }
}
