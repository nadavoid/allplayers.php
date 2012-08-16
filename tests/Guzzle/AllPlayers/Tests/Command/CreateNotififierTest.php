<?php

namespace Guzzle\AllPlayers\Command;

use Guzzle\Tests\GuzzleTestCase;

class CreateNotifierTest extends GuzzleTestCase
{
    public function testCreateNotifier()
    {
        $client = $this->getServiceBuilder()->get('test.allplayers');
        $command = $client->getCommand('CreateNotifier', array(
            'title' => 'test',
            'body' => 'testing'
            )
        );
        $client->execute($command);

        $response = json_decode($command->getResponse()->getBody());
        $this->assertContainsIns('node', $response->uri);
    }
}