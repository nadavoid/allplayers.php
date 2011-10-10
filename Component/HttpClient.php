<?php
namespace AllPlayers\Component;

/**
 * @todo - Replace dependency on apcirest with HttpRequest2 or similar.
 */
require_once dirname(__FILE__) . '/../Legacy/Request-bcm.php';
require_once dirname(__FILE__) . '/../Legacy/RESTClient.php';
require_once dirname(__FILE__) . '/../Legacy/apcirest.php';

class HttpClient extends \apcirest {
  // @todo - This isn't configurable upstream.
  const ENDPOINT = '/api/rest/v1/';

  /**
   * Default AllPlayers.com URL.
   *
   * @var string
   */
  public $base_url = 'https://www.allplayers.com';

  /**
   * @todo - Cleanup constructor on parent (or just use a different parent).
   *
   * @param string $url
   * @param string $user_name
   * @param string $password
   */
  public function __construct($url, $user_name = NULL, $password = NULL) {
    $this->base_url = $url;
    $url_parts = parse_url($url);
    parent::__construct($url_parts['host'], $user_name, $password);

    // @todo - Remove this and use class var at request time.
    $this->proto = $url_parts['scheme'] . '://';

    // @todo - Just extend a REST Class in the future.
    $this->rest = new \RESTClient();

    // Disable logging by default. @todo - Move change upstream.
    // @todo - Create a Drupal watchdog Log class.
    $this->logger->setMask(PEAR_LOG_NONE);
  }

  /**
   * @todo - Choose a path to hit.
   * $_COOKIE['CHOCOLATECHIP']
   *
   * @param array $shared_cookie
   */
  public function ssoSessionInit($cookie_name, $cookie) {
    $this->cookies[$cookie_name] = $cookie;
  }
}
