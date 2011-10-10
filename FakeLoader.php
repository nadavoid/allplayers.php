<?php
/**
 * @file
 * Load *all* AllPlayers library files if you don't have a PSR-0 autoloader.
 *
 * @todo - Stop depending on this.
 */
require_once dirname(__FILE__) . '/Component/HttpClient.php';
require_once dirname(__FILE__) . '/Store/Client.php';
require_once dirname(__FILE__) . '/Store/MockClient.php';
