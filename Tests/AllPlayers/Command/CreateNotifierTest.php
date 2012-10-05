<?php

namespace AllPlayers\Command;

use Guzzle\Tests\GuzzleTestCase;

class CreateNotifierTest extends GuzzleTestCase
{
    public function testCreateNotifier()
    {
        $client = $this->getServiceBuilder()->get('admin.basic');
        $command = $client->getCommand('create_notifier', array(
            'title' => 'test',
            'body' => 'testing'
        ));
        $notifier = $client->execute($command);
        $this->assertContainsIns('node', $notifier['uri']);
    }
}
