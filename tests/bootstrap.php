<?php

error_reporting(E_ALL | E_STRICT);

// Ensure that composer has installed all dependencies
if (!file_exists(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'composer.lock')) {
    die("Dependencies must be installed using composer:\n\ncomposer.phar install --dev\n\n"
        . "See https://github.com/composer/composer/blob/master/README.md for help with installing composer\n");
}

require_once 'PHPUnit/TextUI/TestRunner.php';

// Register an autoloader for the client being tested
spl_autoload_register(function($class) {
    if (0 === strpos($class, 'AllPlayers')) {
        $class = str_replace('AllPlayers', '', $class);
        if ('\\' != DIRECTORY_SEPARATOR) {
            $class = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'AllPlayers' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
        } else {
            $class = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'AllPlayers' . DIRECTORY_SEPARATOR . $class . '.php';
        }
        if (file_exists($class)) {
            require $class;
            return true;
        }
    }

    return false;
});

// Include the composer autoloader
$loader = require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// Register services with the GuzzleTestCase
Guzzle\Tests\GuzzleTestCase::setMockBasePath(__DIR__ . DIRECTORY_SEPARATOR . 'mock');

require_once 'Objects/User.php';
require_once 'User/Fixtures/RandomUser.php';

$service_builder = Guzzle\Tests\GuzzleTestCase::setServiceBuilder(Guzzle\Service\Builder\ServiceBuilder::factory(array(
    'test.allplayers' => array(
        'class' => 'AllPlayers.AllPlayersClient',
        'params' => array(
            'username' => $_SERVER['API_USER'],
            'password' => $_SERVER['API_PASSWORD'],
            'host' => $_SERVER['API_HOST'],
            'curl.CURLOPT_SSL_VERIFYHOST' => false,
            'curl.CURLOPT_SSL_VERIFYPEER' => false
        )
    )
)));
