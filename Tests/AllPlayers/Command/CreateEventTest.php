<?php

namespace AllPlayers\Command;

use Guzzle\Tests\GuzzleTestCase;

class CreateEventTest extends GuzzleTestCase
{
    public function testCreateEvent()
    {
        $client = $this->getServiceBuilder()->get('admin.basic');
        $random_event = array(
            'groups' => array(
                0 => '0f16af3c-aab1-11e1-b16a-12313d186528'
            ),
            'title' => 'php client test',
            'description' => 'testing',
            'date_time' => array(
                'start' => '2012-03-30T19:54:00',
                'end' => '2012-03-30T20:54:00'
            ),
            'external_id' => 'CLIENT_TEST'
        );
        $command = $client->getCommand('create_event', $random_event);
        $event = $client->execute($command);
        $this->assertEquals($event['title'], $random_event['title']);
    }
}
