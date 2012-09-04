<?php

namespace AllPlayers\Command;

use Guzzle\Tests\GuzzleTestCase;

class CreateNotifierTest extends GuzzleTestCase
{
    public function testCreateNotifier()
    {
        $client = $this->getServiceBuilder()->get('test.allplayers');
        $command = $client->getCommand('create_notifier', array(
            'title' => 'test',
            'body' => 'testing'
            )
        );
        $client->execute($command);

        $response = json_decode($command->getResponse()->getBody());
        $this->assertContainsIns('node', $response->uri);
    }
}