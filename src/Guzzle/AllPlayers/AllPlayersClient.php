<?php

namespace Guzzle\AllPlayers;

use Guzzle\Service\Client;
use Guzzle\Service\Inspector;
use Guzzle\Service\Description\ServiceDescription;
use Guzzle\Http\Plugin\CurlAuthPlugin;

class AllPlayersClient extends Client
{
    /**
    * @var string Username
    */
    protected $username;

    /**
     * @var string Password
     */
    protected $password;
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
            'base_url' => '{scheme}://{host}/api/v{version}/rest',
            'scheme' => 'https',
            'host' => 'www.allplayers.com',
            'version' => '1'
        );
        $required = array('username', 'password', 'base_url');
        $config = Inspector::prepareConfig($config, $default, $required);

        $client = new self(
            $config->get('base_url'),
            $config->get('username'),
            $config->get('password')
        );
        $client->setConfig($config);
        return $client;
    }

    /**
     * Client constructor
     *
     * @param string $baseUrl Base URL of the web service
     * @param string $username API username
     * @param string $password API password
     */
    public function __construct($baseUrl, $username, $password) {
        parent::__construct($baseUrl);
        $this->username = $username;
        $this->password = $password;
        $authPlugin = new CurlAuthPlugin($this->username, $this->password);

        // Add the auth plugin to the client object
        $this->addSubscriber($authPlugin);
    }
}
