<?php

namespace AllPlayers\Command;

use Guzzle\Service\Command\DynamicCommand;
use Guzzle\Http\Url;
use Guzzle\Parser\ParserRegistry;
use Guzzle\Service\Command\LocationVisitor\LocationVisitorInterface;
use Guzzle\Service\Command\LocationVisitor\BodyVisitor;
use Guzzle\Service\Command\LocationVisitor\HeaderVisitor;
use Guzzle\Service\Command\LocationVisitor\JsonBodyVisitor;
use Guzzle\Service\Command\LocationVisitor\QueryVisitor;
use Guzzle\Service\Command\LocationVisitor\PostFieldVisitor;
use Guzzle\Service\Command\LocationVisitor\PostFileVisitor;
use AllPlayers\Command\LocationVisitor\BodyArrayVisitor;

/**
 * AllPlayers command which interacts with AllPlayers.com API.
 */
class AllPlayersCommand extends DynamicCommand
{

   /**
     * Prepare the default and static settings of the command
     */
    protected function initConfig()
    {
        foreach ($this->apiCommand->getParams() as $name => $arg) {
            $currentValue = $this->get($name);
            $configValue = $arg->getValue($currentValue);
            if ($currentValue !== $configValue) {
                $this->set($name, $configValue);
            }
        }
        $this->set('headers', array('Accept' => $this->get('response_type')));
    }

    /**
     * Initialize the command by adding the default visitors.
     */
    protected function init()
    {
        if (!self::$visitorCache) {
            self::$visitorCache = array(
                'header'     => new HeaderVisitor(),
                'query'      => new QueryVisitor(),
                'body'       => new BodyVisitor(),
                'json'       => new JsonBodyVisitor(),
                'post_file'  => new PostFileVisitor(),
                'post_field' => new PostFieldVisitor(),
                'body_array'   => new BodyArrayVisitor(),
            );
        }

        $this->visitors = self::$visitorCache;
    }

    /**
     * {@inheritdoc}
     */
    protected function build()
    {
        $body_params = array();
        $uri = $this->apiCommand->getUri();

        if (!$uri) {
            $url = $this->client->getBaseUrl();
        } else {
            // Get the path values and use the client config settings
            $variables = $this->client->getConfig()->getAll();
            foreach ($this->apiCommand->getParams() as $name => $arg) {
                $configValue = $this->get($name);
                if (is_scalar($configValue)) {
                    $variables[$name] = $arg->getPrepend() . $configValue . $arg->getAppend();
                }
            }
            // Merge the client's base URL with an expanded URI template
            $url = (string) Url::factory($this->client->getBaseUrl())
                ->combine(ParserRegistry::get('uri_template')->expand($uri, $variables));
        }

        // Inject path and base_url values into the URL
        $this->request = $this->client->createRequest($this->apiCommand->getMethod(), $url);

        // Add arguments to the request using the location attribute
        foreach ($this->apiCommand->getParams() as $name => $arg) {
            $location = $arg->getLocation();
            // Visit with the associated visitor
            if (isset($this->visitors[$location]) && $location = 'body_array') {
                $value = $this->get($name);
                if ($value !== null) {
                    $body_params = array_merge($body_params, array($name => $value));
                }
            }
            elseif (isset($this->visitors[$location])) {
                // Ensure that a value has been set for this parameter
                $configValue = $this->get($name);
                if ($configValue !== null) {
                    // Create the value based on prepend and append settings
                    if ($arg->getPrepend() || $arg->getAppend()) {
                        $value = $arg->getPrepend() . $configValue . $arg->getAppend();
                    } else {
                        $value = $configValue;
                    }
                    // Apply the parameter value with the location visitor
                    $this->visitors[$location]->visit($this, $this->request, $arg->getLocationKey() ?: $name, $value);
                }
            }
        }

        if (!empty($body_params)) {
            $this->visitors[$location]->visit($this, $this->request, 'body_array', $body_params);
        }

        // Call the after method on each visitor
        foreach ($this->visitors as $visitor) {
            $visitor->after($this, $this->request);
        }
    }
}