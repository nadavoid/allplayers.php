<?php

namespace AllPlayers\Command;

use Guzzle\Tests\GuzzleTestCase;
use AllPlayers\Tests\User\Fixtures\RandomUser;
use Guzzle\Common\Event;

class CreateUserTest extends GuzzleTestCase
{
    public function testCreateUser()
    {
        $client = $this->getServiceBuilder()->get('admin.basic');
        $client->getEventDispatcher()->addListener('request.error', function(Event $event) {
            if ($event['response']->getStatusCode() == 406)
            {
                $newRequest = clone $event['request'];
                $response = json_decode($event['response']->getBody(true), 'application/json');
                list($number_1, $number_2, $equals) = preg_split('/[+\=]/', $response['captcha_error']['captcha_problem']);
                $solution = (int) $number_1 + (int) $number_2;
                $newRequest->setHeader('x-allplayers-captcha-token', $response['captcha_error']['captcha_token']);
                $newRequest->setHeader('x-allplayers-captcha-solution', $solution);

                $newResponse = $newRequest->send();
                // Set the response object of the request without firing more events
                $event['response'] = $newResponse;
                $event->stopPropagation();
            }
        });
        $random_user = new RandomUser();
        $command = $client->getCommand('create_user', (array) $random_user);
        $user = $client->execute($command);
        $this->assertEquals($user->firstname, $random_user->firstname);
        $this->assertEquals($user->lastname, $random_user->lastname);
        $this->assertEquals($user->email, $random_user->email);
        $this->assertEquals(substr($user->gender, 0, 1), $random_user->gender);
    }
}
