<?php

namespace AllPlayers;

use Guzzle\Service\Client;
use Guzzle\Service\Inspector;
use Guzzle\Service\Description\ServiceDescription;
use Guzzle\Http\Plugin\CurlAuthPlugin;
use Guzzle\Http\Plugin\OauthPlugin;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AllPlayersClient extends Client
{
    /**
     * Factory method to create a new AllPlayersClient.
     *
     * @param array|Collection $config Configuration data. Array keys:
     *    base_url - Base URL of web service
     *
     * @return AllPlayersClient
     */
    public static function factory($config = array())
    {
        $default = array(
            'auth' => 'basic',
            'base_url' => '{scheme}://{host}/api/v{version}/rest',
            'scheme' => 'https',
            'host' => 'www.allplayers.com',
            'version' => '1'
        );
        $required = array('base_url');
        $config = Inspector::prepareConfig($config, $default, $required);
        $auth_method = $config->get('auth');
        switch ($auth_method) {
            case 'basic':
                $auth = new CurlAuthPlugin($config->get('username'), $config->get('password'));
                break;
            case 'oauth':
                $auth = new OauthPlugin($config->get('oauth'));
                break;
        }
        $client = new self($config->get('base_url'), $auth);
        $client->setConfig($config);

        return $client;
    }

    /**
     * Client constructor
     *
     * @param string $baseUrl Base URL of the web service
     * @param object $auth    Authentication object
     */
    public function __construct($baseUrl, EventSubscriberInterface $auth)
    {
        parent::__construct($baseUrl);

        $description = ServiceDescription::factory(__DIR__ . DIRECTORY_SEPARATOR . 'client.json');
        $this->setDescription($description);
        // Add the auth plugin to the client object
        $this->addSubscriber($auth);
    }
}
