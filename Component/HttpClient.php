<?php
namespace AllPlayers\Component;

use RESTClient;

use ErrorException;
use InvalidArgumentException;
use Log;

/**
 * @todo Replace dependency on Request-bcm and RESTClient with HttpRequest2
 * or similar.
 */
require_once dirname(__FILE__) . '/../Legacy/Request-bcm.php';
require_once dirname(__FILE__) . '/../Legacy/RESTClient.php';

/**
 * Handy RESTful wrapper.
 */
class HttpClient
{
    /**
     * AllPlayers.com endpoint URL. e.g. https://www.allplayers.com/api/v1/rest.
     *
     * @var string
     */
    public $urlPrefix = null;

    /**
     * Format string.
     *
     * @var string
     *
     * @todo Make this a mime-type.
     */
    public $format = 'json';

    /**
     * RESTClient object.
     *
     * @deprecated
     *
     * @var RESTClient
     *
     * @todo This should be wrapped/extended by the main class.
     */
    public $rest = null;

    /**
     * Control wheter or not to print debug information. Use with care, may dump
     * sensetive information.
     *
     * @var bool
     */
    public $debug = false;

    /**
     * Log instance to control log information generated during a request.
     *
     * @var Log
     */
    public $logger = null;

    /**
     * HTTP Response code of last request.
     *
     * @var int
     */
    public $responseCode = null;

    /**
     * Cookies to be reused between requests.
     *
     * @var string
     */
    public $cookies = array();

    /**
     * Headers variable for setting on an http request.
     *
     * @var array
     */
    protected $headers = array();

    /**
     * @param string $url
     *   e.g. https://www.allplayers.com/api/v1/rest.
     * @param Log $logger
     *   (optional)
     *
     * @todo Just extend a REST Class in the future.
     */
    public function __construct($url_prefix, Log $logger = null)
    {
        // Validate $url argument.
        if (!filter_var($url_prefix, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            throw new InvalidArgumentException(
                'Invalid argument 1: url_prefix must be a full URL, including path to an API endpoint.'
            );
        }
        $this->urlPrefix = $url_prefix;

        // TODO: Just extend a REST Class in the future.
        $this->rest = new RESTClient();

        // Handle $logger argument.
        if (isset($logger)) {
            $this->logger = $logger;
        } else {
            // Disable logging by default.
            $this->logger = Log::singleton('console', '', __CLASS__, PEAR_LOG_DEBUG);
            $this->logger->setMask(PEAR_LOG_NONE);
        }
    }

    /**
     * Adds headers to the http request.
     *
     * @param string $key
     *   e.g. "User-Agent"
     * @param string $val
     *   e.g. "Chrome"
     */
    public function addHeader($key, $val)
    {
        $this->headers[$key] = $val;
    }

    /**
     * Handle all RESTful requests.
     *
     * @param string $verb
     * @param string $path
     * @param array $query
     * @param mixed $params
     * @param array $headers
     * @param boolean $allow_redirects
     *
     * @return array|stdClass
     *   Array or object from decodeResponse().
     */
    private function httpRequest(
        $verb,
        $path,
        $query = array(),
        $params = null,
        $headers = array(),
        $allow_redirects = true
    ) {
        $url = "$this->urlPrefix/$path";

        if (!empty($query)) {
            $url .= '?' . http_build_query($query, 0, '&');
        }

        $this->rest->createRequest($url, $verb, null, $allow_redirects);
        $this->rest->setBody(json_encode($params));
        $this->rest->addHeader('Cache-Control', 'no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->rest->addHeader('Accept', 'application/json');
        $this->rest->addHeader('Content-Type', 'application/json');
        $headers = array_merge($this->headers, $headers);
        foreach ($headers as $key => $value) {
            $this->rest->addHeader($key, $value);
        }
        $this->addCookies();

        $this->logger->info("HTTP $verb: $url");
        $this->rest->sendRequest();

        $this->responseCode = $this->rest->responseCode;
        $this->responseBody = $this->rest->getResponse();
        if ((int) $this->responseCode >= 400) {
            if ($this->debug) {
                $this->logger->debug(print_r($this->rest, true));
            }
            $this->logger->err("HTTP $this->responseCode from $url");
            throw new ErrorException("HTTP $this->responseCode $this->responseBody", $this->responseCode);
        }

        $this->logger->info("HTTP $this->responseCode from $url");

        return $this->decodeResponse();
    }

    /**
     * GET data from REST server.
     *
     * @param string $path
     *   Path to append to base to form the URI.
     * @param array $query
     *   Items to append to path as a query string.
     * @param array $headers
     *   Additional headers.
     * @param boolean $allow_redirects
     *
     * @return array
     *   Array from process_response().
     *
     * @todo $headers is not used.
     */
    public function get($path, $query = array(), $headers = array(), $allow_redirects = true)
    {
        return $this->httpRequest('GET', $path, $query, null, $headers, $allow_redirects);
    }

    /**
     * POST data to REST server.
     *
     * @param string $path
     *   Path to append to base to form the URI.
     * @param array $params
     *   Parameters to post.
     * @param array $headers
     *   Additional headers.
     *
     * @return array
     *   Array from process_response().
     *
     * @todo $headers is not used.
     */
    public function post($path, $params = array(), $headers = array())
    {
        return $this->httpRequest('POST', $path, null, $params, $headers);
    }

    /**
     * PUT data to REST server.
     *
     * @param string $path
     *   Path to append to base to form the URI.
     * @param array $params
     *   Parameters to put.
     * @param array $headers
     *   Additional headers.
     *
     * @return array
     *   Array from process_response().
     *
     * @todo $headers is not used.
     */
    public function put($path, $params = array(), $headers = array())
    {
        return $this->httpRequest('PUT', $path, null, $params, $headers);
    }

    /**
     * DELETE data from REST server.
     *
     * @param string $path
     *   Path to append to base to form the URI.
     * @param array $query
     *   Items to append to path as a query string.
     * @param array $headers
     *   Additional headers.
     *
     * @return
     *   Array from process_response().
     *
     * @todo $headers is not used.
     */
    public function delete($path, $query = array(), $headers = array())
    {
        return $this->httpRequest('DELETE', $path, $query, null, $headers);
    }

    /**
     * Process the response.
     *
     * @return mixed
     *   Decoded response from the last rest request.
     */
    public function decodeResponse()
    {
        switch ($this->format) {
            case 'json':
            default:
                $ret = json_decode($this->rest->getResponse(), false);
                // Bubble up decode errors.
                if (json_last_error() !== JSON_ERROR_NONE) {
                  $this->logger->info('Invalid JSON: ' . $this->rest->getResponse());
                  throw new ErrorException('Failed to decode JSON response.', json_last_error());
                }
                return $ret;
        }
    }

    /**
     * Add stored cookies to next request.
     */
    public function addCookies()
    {
        if (isset($this->session)) {
            $this->rest->addCookie($this->session['session_name'], $this->session['sessid']);
        }
        foreach ($this->cookies as $cookie) {
            $this->rest->addCookie($cookie['name'], $cookie['value']);
        }
    }

    /**
     * Store cookies from last response.
     *
     * @todo Review proper cookie handling.
     */
    public function storeCookies()
    {
        $this->cookies = $this->rest->getResponseCookies();
    }

    /**
     * Helper function to get all items from an index endpoint.
     *
     * @param string $path
     *   Relative path to the endpoint. (e.g. /users).
     * @param array $query
     *   (Optional) URL Query parameters. Many endpoints take filters in
     *   'parameters' array.
     * @param string $fields
     *   (Optional) Specify fields you'd like the resource to return (e.g.
     *   title, status).
     * @param integer|string $page
     *   (Optional) Numeric page number or '*' to fetch all pages. Default to 0.
     *   NOTE: The '*' parameter is a simple helper for basic CLI usage, using
     *   this loop is not recommended as it could easily cause a timeout or
     *   out-of-memory error.
     * @param integer $page_size
     *   (Optional) Limit the number of results returned per page. If not set,
     *   then we default to 20.
     *   NOTE: This does not limit the overall return set when using the '*'
     *   page parameter.
     *
     * @return array
     *   Array containing the stdObjects the index lists.
     */
    public function index($path, $query = array(), $fields = null, $page = 0, $page_size = 20)
    {
        $query['fields'] = $fields;
        $query['page'] = $page;
        $query['pagesize'] = (isset($page_size)) ? $page_size : 20;

        // "limit" was renamed to "pagesize", maintain both for backwards
        // compatibility.
        $query['limit'] = $query['pagesize'];

        // Page specified, get only that page.
        if (is_numeric($page)) {
            return $this->get($path, array_filter($query));
        } elseif ($page != '*') {
            throw new InvalidArgumentException('Invalid argument 4: page must be an integer or "*".');
        }

        // Page *, loop to get all.
        $query['page'] = 0;
        $results = array();

        // Index loop.
        do {
            // Get current page.
            $page_results = $this->get($path, array_filter($query));

            // Merge into overall result.
            $results = array_merge($results, (array) $page_results);
            $query['page']++;

            // If the result count != to pagesize, we are on the last page and
            // stop looping.
        } while (count($page_results) == $query['pagesize']);

        return $results;
    }

    /**
     * $_COOKIE['CHOCOLATECHIP']
     *
     * @param string $cookie_name
     * @param string $cookie
     * @param string $auth_path
     *
     * @todo Choose a path to hit.
     */
    public function ssoSessionInit($cookie_name, $cookie, $auth_path = 'group_stores')
    {
        $this->cookies[] = array('name' => $cookie_name, 'value' => $cookie);

        $this->get($auth_path, array(), array(), false);
        $this->storeCookies();
    }

    /**
     * @param string $username
     * @param string $password
     */
    public function authSessionInit($username = null, $password = null)
    {
        $this->userLogin($username, $password);
    }
}
