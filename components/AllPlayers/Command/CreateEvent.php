<?php

namespace AllPlayers\Command;

use Guzzle\Service\Command\AbstractCommand;

/**
 * Sends an API request to create an event.
 *
 * @guzzle groups      type="array" required="true" doc="Groups(uuids) involved in event."
 * @guzzle title       type="string" required="true" doc="Title of event."
 * @guzzle description type="string" required="true" doc="Description of event."
 * @guzzle date_time   type="array" required="true" doc="Date and time of start and end of event, e.g. date_time[start] 2012-03-30T19:54:00."
 * @guzzle category    type="string" doc="Category of the event, e.g. game."
 * @guzzle resources   type="array" doc="Resources(uuids) where this event takes place."
 * @guzzle competitors type="array" doc="Associative array of competitor gid : competitor label : competitor score."
 * @guzzle published   type="string" doc="Published status, e.g. TRUE."
 * @guzzle external_id type="string" doc="External ID up to 72 characters to be used for relating to external content."
 */
class CreateEvent extends AbstractCommand
{
    protected function build()
    {
        $params = $this->getAll(array(
            'groups', 'title', 'description', 'date_time', 'category', 'resources', 'competitors', 'published', 'external_id'));
        $this->request = $this->client->post('events');
        $this->request->setBody(json_encode($params), 'application/json');
    }
}
