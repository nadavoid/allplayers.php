<?php

namespace AllPlayers\Command;

use Guzzle\Tests\GuzzleTestCase;

class UpdateEventTest extends GuzzleTestCase
{
    public function testUpdateEvent()
    {
        $client = $this->getServiceBuilder()->get('admin.basic');
        $updates = array(
            'external_id' => 'updating_from_guzzle',
            'uuid' => '28500664-033c-11e2-bc4d-005056b1e1d9'
        );
        $command = $client->getCommand('update_event', $updates);
        $event = $client->execute($command);
        $this->assertEquals($event['external_id'], $updates['external_id']);
    }
}
