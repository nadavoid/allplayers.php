<?php
/**
 * Class for communicating with Drupal Services 3.x
 *
 * @todo - Remove AllPlayers specific code, rename class.
 * @todo - Remove actual Drupal functions (node_save, watchdog).
 * @todo - Consider extending an existing RESTful class.
 *
 * @todo - Abstract HTTP response out of apcirest to a seperate class.
 * @see http://php.net/manual/en/class.httpresponse.php
 *
 * @todo - Document why we aren't using PEAR HTTP or HTTP_Request2 (or PECL HTTP classes).
 * @see http://pear.php.net/manual/en/package.http.http-request.php
 */

//require_once "HTTP/Request.php";
require_once "Request-bcm.php";
require_once 'RESTClient.php';
// pear install Log
require_once 'Log.php';

// Suppress redundant Log DateTime errors
date_default_timezone_set(@date_default_timezone_get());

class apcirest {

  public $host = "allplayers:8080";
  public $proto = "http:";
  private $user_name = "user";
  private $password = "password";
  public  $rest   = null;
  public  $debug = "";
  public  $type   = "";
  public  $title   = "";
  public  $output = "";
  public  $bids  = array ();
  public  $plids = array ();
  public  $map_vals = array ();
  public $cookies = array();
  public $uuid;

  // map nid,title,bid,mlid,path (for mapping from src->dest)
  // map all data by nid
  static $nid_vals = array ();
  // map title to nid
  static $title_vals = array ();
  // map mlid to nid
  // used for parent link id (plid) mapping between machines
  // these don't use nid as books (bid) do ,
  // but use mlid , menu link id?
  static $mlid_vals = array ();

  // for $nid_map  index is src NID, and value is dest_nid
  //  so have a mapping from nid to nid on machines
  //  also used for mapping bid from one machine to another machine
  //   static $nid_map  = array();

  public function __construct($host = "localhost", $user_name = "user", $password = "password", $proto="http://", $req, $type = "apcipage", $debug) {
    $this->format = 'json';
    $this->user_name = $user_name;
    $this->password = $password;
    $this->host	= $host;
    $this->proto    = $proto;
    $this->rest	= $req;
    $this->sess  = NULL;
    $this->session = NULL;
    $this->session_name = NULL;
    $this->debug = $debug;
    $this->type  = $type;
    $this->output = "";
    $this->responseCode  = 0;
    $this->title  = "";
    $this->bids   = isset($bids) ? $bids : NULL;
    $this->plids   = isset($plids) ? $plids : NULL;
    $this->map_vals   = isset($map_vals) ? $map_vals : NULL;

    if (isset($logger)) {
      $this->logger = $logger;
    }
    else {
      // This logging level may be too high for default.
      $this->logger = Log::singleton('console', '', 'apcirest', PEAR_LOG_DEBUG);
      $this->logger->setMask(Log::MAX(PEAR_LOG_INFO));
    }
  }

  /**
   * Handle all RESTful requests.
   *
   * @param unknown_type $verb
   * @param unknown_type $path
   * @param unknown_type $params
   * @param unknown_type $headers
   * @return
   *   array or object from decodeResponse().
   */
  private function httpRequest($verb, $path, $query, $params = array(), $headers = array(), $allow_redirects = TRUE) {
    $url = $this->proto . $this->host . "/api/v1/rest/" . $path;
    if (!empty($query)) {
      $url .= '?' . http_build_query($query);
    }
    $this->rest->createRequest($url, $verb, NULL, $allow_redirects);
    $this->rest->setBody(json_encode($params));
    $this->rest->addHeader("Cache-Control",'no-cache, must-revalidate, post-check=0, pre-check=0');
    $this->rest->addHeader("Accept",'application/json');
    $this->rest->addHeader('Content-Type', 'application/json');
    $this->addCookies();

    $this->logger->info("HTTP $verb: $url");
    $this->rest->sendRequest();

    $this->responseCode = $this->rest->responseCode;
    if ((int)$this->responseCode >= 400) {
      if ($this->debug) {
        print_r($this->rest);
      }
      $this->logger->err("HTTP $this->responseCode from $url");
      throw new ErrorException('HTTP ' . $this->responseCode, $this->responseCode);
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
   *   Additional headers. @todo - this isn't used.
   *
   * @return
   *   array from process_response().
   */
  public function get($path, $query = array(), $headers = array(), $allow_redirects = TRUE) {
    return $this->httpRequest('GET', $path, $query, NULL, $headers, $allow_redirects);
  }

  /**
   * POST data to REST server.
   *
   * @param string $path
   *   Path to append to base to form the URI.
   * @param array $params
   *   Parameters to
   * @param array $headers
   *   Additional headers. @todo - this isn't used.
   *
   * @return
   *   array from process_response().
   */
  public function post($path, $params = array(), $headers = array()) {
    return $this->httpRequest('POST', $path, NULL, $params, $headers);
  }

  /**
   * PUT data to REST server.
   *
   * @param string $path
   *   Path to append to base to form the URI.
   * @param array $params
   *   Parameters to
   * @param array $headers
   *   Additional headers. @todo - this isn't used.
   *
   * @return
   *   array from process_response().
   */
  public function put($path, $params = array(), $headers = array()) {
    return $this->httpRequest('PUT', $path, NULL, $params, $headers);
  }

  /**
   * DELETE data from REST server.
   *
   * @param string $path
   *   Path to append to base to form the URI.
   * @param array $query
   *   Items to append to path as a query string.
   * @param array $headers
   *   Additional headers. @todo - this isn't used.
   *
   * @return
   *   array from process_response().
   */
  public function delete($path, $query = array(), $headers = array()) {
    return $this->httpRequest('DELETE', $path, $query, NULL, $headers);
  }

  /**
   * Process the response.
   *
   * @return mixed
   *   Decoded response from the last rest request.
   */
  public function decodeResponse() {
    switch ($this->format) {
      case 'json':
      default:
        return json_decode($this->rest->getResponse(), FALSE);
    }
  }

  /**
   * Add stored cookies to next request.
   */
  public function addCookies() {
    if (isset($this->session)) {
      $this->rest->addCookie($this->session['session_name'], $this->session['sessid']);
    }
    foreach ($this->cookies as $cookie) {
      $this->rest->addCookie($cookie['name'], $cookie['value']);
    }
  }

  /**
   * Store cookies from last response.
   * @todo - Review proper cookie handling.
   */
  public function storeCookies() {
    $this->cookies = $this->rest->getResponseCookies();
  }

  /* ----- Start Drupal Services endpoints ----- */

  /**
   * Helper function to get all nodes from an index endpoint.
   *
   * @param string $path
   * @param array $parameters
   * @param string $fields
   * @param mixed $page
   *   Numeric page number or '*' to fetch all pages.
   */
  public function index($path, $parameters = NULL, $fields = NULL, $page = 0, $limit = NULL) {
    $query = array(
      'fields' => $fields,
      'page' => $page,
      'limit' => $limit,
    );
    if ($parameters){
      foreach ($parameters as $key => $value){
        $query[$key] = $value;
      }
    }
    // Page specified, get only that page.
    if (is_numeric($page)) {
      return $this->get($path, array_filter($query));
    }
    // Page *, loop to get all.
    $query['page'] = 0;
    $results = array();

    // Index loop.
    do {
      $page_results = $this->get($path, array_filter($query));
      if (is_object($page_results)){
        foreach ($page_results as $key => $value){
          $results[$key] = $value;
        }
      }
      else{
      $results = array_merge($results, $page_results);
      }
      $query['page']++;
    } while (count($page_results) == $limit);

    return $results;
  }


  /**
   * Fetch a user by uuid.
   *
   * @param $uuid
   * @return object
   *   user object.
   */
  public function userGetUser($uuid, $fields = NULL) {
    $query = array('fields' => $fields);
    return $this->get("users/$uuid");
  }

  /**
   * Create a user
   *
   * @param $firstname
   * @param $lastname
   * @param $email
   * @param $gender
   * @param $birthday
   * @param $password
   * @return object
   *   user object
   */
  public function userCreateUser($firstname, $lastname, $email, $gender, $birthday, $password) {
    return $this->post("users", array(
      'firstname' => $firstname,
      'lastname'  => $lastname,
      'email'     => $email,
      'gender'    => $gender,
      'birthday'  => $birthday,
      'password'  => $password
    ));
  }

  /**
   * List users groups based on parameters
   *
   * @param int $uuid
   *  user uuid
   *
   * @param string $fields
   *  comma separated string of fields to retrieve
   *
   * @param int $page
   *  which page to call
   *
   * @param int $limit
   *  how many results to return per page
   */
  public function userGetMyGroups($uuid, $fields = NULL, $page = 0, $limit = NULL) {
    //compile path
    $path = 'users/'.$uuid.'/groups';
    return $this->index($path, $parameters = NULL, $fields, $page, $limit);
  }

  /**
   * List users groupmates based on parameters
   *
   * @param int $uuid
   *  user uuid
   *
   * @param string $fields
   *  comma separated string of fields to retrieve
   *
   * @param int $page
   *  which page to call
   *
   * @param int $limit
   *  how many results to return per page
   */
  public function userGetGroupmates($uuid, $fields = NULL, $page = 0, $limit = NULL) {
    //compile path
    $path = 'users/'.$uuid.'/groupmates';
    return $this->index($path, $parameters = NULL, $fields, $page, $limit);
  }

  /**
   * List users friends based on parameters
   *
   * @param int $uuid
   *  user uuid
   *
   * @param string $fields
   *  comma separated string of fields to retrieve
   *
   * @param int $page
   *  which page to call
   *
   * @param int $limit
   *  how many results to return per page
   */
  public function userGetFriends($uuid, $fields = NULL, $page = 0, $limit = NULL) {
    //compile path
    $path = 'users/'.$uuid.'/friends';
    return $this->index($path, $parameters = NULL, $fields, $page, $limit);
  }

 /**
   * List users events based on parameters
   *
   * @param string $uuid
   *  user uuid
   *
   * @param string $fields
   *  comma separated string of fields to retrieve
   *
   * @param int $page
   *  which page to call
   *
   * @param int $limit
   *  how many results to return per page
   */
  public function userGetEvents($uuid, $fields = NULL, $page = 0, $limit = NULL) {
    //compile path
    $path = 'users/'.$uuid.'/events';
    return $this->index($path, $parameters = NULL, $fields, $page, $limit);
  }

  /**
   * List users upcoming events based on parameters
   *
   * @param int $uuid
   *  user uuid
   *
   * @param string $fields
   *  comma separated string of fields to retrieve
   *
   * @param int $page
   *  which page to call
   *
   * @param int $limit
   *  how many results to return per page
   */
  public function userGetEventsUpcoming($uuid, $fields = NULL, $page = 0, $limit = NULL) {
    //compile path
    $path = 'users/'.$uuid.'/events/upcoming';
    return $this->index($path, $parameters = NULL, $fields, $page, $limit);
  }

  /**
   * Login via user endpoint.
   *
   * @param string $user
   *  username
   * @param string $pass
   *  password
   */
  public function userLogin($user, $pass) {
    $ret = $this->post('users/login', array('username' => $user, 'password' => $pass));
    $this->session = array('session_name' => $ret->session_name, 'sessid' => $ret->sessid);
    return $ret;
  }

  /**
   * Logout via user endpoint.
   */
  public function userLogout() {
    $ret = $this->post('users/logout');
    $this->sess = NULL;
    $this->session_name = NULL;
    return $ret;
  }

  /**
   * Retrieve a specific group
   *
   * @param int $uuid
   *  group uuid
   *
   * @param string $fields
   *  comma separated string of fields to retrieve
   */
  public function groupsGetGroup($uuid, $fields = NULL) {
    //compile path
    $path = 'groups/'.$uuid;
    $query = array();
    if ($fields){
      $query['fields'] = $fields;
    }
    return $this->get($path, $query);
  }

  /**
   * List groups based on parameters
   *
   * @param string $search
   *  a search term to search for
   *
   * @param string zip
   *  a zip code to search around
   *
   * @param int $search_distance
   *  how big the radius of the search from the zipcode should be
   *
   * @param string $search_units
   *  what units to use for search_distance
   *
   * @param string $fields
   *  comma separated list of fields that need to come back
   *
   * @param int $limit
   *  how many results to retrieve per page
   *
   * @param mixed $page
   *  what page of the results to call
   */
  public function groupsIndex($search = NULL, $zip = NULL, $search_distance = 10, $search_units = 'mile', $fields = NULL, $limit = NULL, $page = 0) {
    //compile path
    $path = 'groups';
    $parameters = array(
      'search' => $search,
      'distance' => array(
        'postal_code' => $zip,
        'search_distance' => $search_distance,
        'search_units' => $search_units,
      ),
      'feature' => 'All',
    );
    return $this->index($path, $parameters, $fields, $page, $limit);
  }

  /**
   * List a groups members based on parameters
   *
   * @param int $uuid
   *   group uuid
   *
   * @param string $fields
   *   Optional comma seperated list of fields to return.
   *
   * @param int $page
   *  Page of items to return, up to 20 per page.
   *
   * @param int $limit
   *  how many results should come back per page
   */
  public function groupsGetMembers($uuid, $fields = NULL, $page = 0, $limit = NULL){
  //compile path
    $path = 'groups/'.$uuid.'/members';
    return $this->index($path, $parameters = NULL, $fields, $page, $limit);
  }

 /**
   * List a groups albums based on parameters
   *
   * @param int $uuid
   *   group uuid
   *
   * @param string $fields
   *   Optional comma seperated list of fields to return.
   *
   * @param int $page
   *  Page of items to return, up to 20 per page.
   *
   * @param int $limit
   *  how many results should come back per page
   */
  public function groupsGetAlbums($uuid, $fields = NULL, $page = 0, $limit = NULL) {
    //compile path
    $path = 'groups/'.$uuid.'/albums';
    return $this->index($path, $parameters = NULL, $fields, $page, $limit);
  }

  /**
   * List a groups photos based on parameters
   *
   * @param int $uuid
   *   group uuid
   *
   * @param string $fields
   *   Optional comma seperated list of fields to return.
   *
   * @param int $page
   *  Page of items to return, up to 20 per page.
   *
   * @param int $limit
   *  how many results should come back per page
   */
  public function groupsGetPhotos($uuid, $fields = NULL, $page = 0, $limit = NULL) {
    //compile path
    $path = 'groups/'.$uuid.'/photos';
    return $this->index($path, $parameters = NULL, $fields, $page, $limit);
  }


   /**
   * List a groups events based on parameters
   *
   * @param int $uuid
   *   group uuid
   *
   * @param string $fields
   *   Optional comma seperated list of fields to return.
   *
   * @param int $page
   *  Page of items to return, up to 20 per page.
   *
   * @param int $limit
   *  how many results should come back per page
   */
  public function groupsGetEvents($uuid, $fields = NULL, $page = 0, $limit = NULL) {
    //compile path
    $path = 'groups/'.$uuid.'/events';
    return $this->index($path, $parameters = NULL, $fields, $page, $limit);
  }


  /**
   * List a groups upcoming based on parameters
   *
   * @param int $uuid
   *   group uuid
   *
   * @param string $fields
   *   Optional comma seperated list of fields to return.
   *
   * @param int $page
   *  Page of items to return, up to 20 per page.
   *
   * @param int $limit
   *  how many results should come back per page
   */
  public function groupsGetEventsUpcoming($uuid, $fields = NULL, $page = 0, $limit = NULL) {
    //compile path
    $path = 'groups/'.$uuid.'/events/upcoming';
    return $this->index($path, $parameters = NULL, $fields, $page, $limit);
  }


  /**
   * Returns a specific album's photos based on parameters
   *
   * @param int $uuid
   *   album uuid
   *
   * @param string $fields
   *   Optional comma seperated list of fields to return.
   *
   * @param int $page
   *  Page of items to return, up to 20 per page.
   *
   * @param int $limit
   *  how many results should come back per page
   */
  public function albumsGetPhotos($uuid, $fields = NULL, $page = 0, $limit = NULL) {
    //compile path
    $path = 'albums/'.$uuid.'/photos';
    return $this->index($path, $parameters = NULL, $fields, $page, $limit);
  }

  /**
   * Return a specific photo based on parameters
   *
   * @param int $id
   *  photo id that should be retrieved
   *
   * @param string $fields
   *  Optional comma seperated list of fields to return.
   */
  public function photosGetPhoto($pid, $fields = NULL) {
    //compile path
    $path = 'photos/'.$pid;
    $query = array('fields' => $fields);
    return $this->get($path, $query);
  }

  /**
   * Return a specific event
   *
   * @param int $eid
   *  event id
   *
   * @param string $fields
   *  Optional comma seperated list of fields to return.
   */
  public function eventsGetEvent($eid, $fields = NULL) {
    //compile path
    $path = 'events/'.$eid;
    $query = array();
    if ($fields){
      $query['fields'] = $fields;
    }
    return $this->get($path, $query);
  }

  /**
   * Return a specific resource based on parameters
   *
   * @param int $rid
   *  resource id
   *
   * @param string $fields
   *  Optional comma seperated list of fields to return.
   */
  public function resourcesGetResource($uuid, $fields = NULL) {
    //compile path
    $path = 'resources/'.$uuid;
    $query = array();
    if ($fields){
      $query['fields'] = $fields;
    }
    return $this->get($path, $query);
  }

  /**
   * Create a resource
   *
   * @param $node
   * @return object
   *   resource object
   */
  public function resourceCreate($node) {
    return $this->post("resources", $node);
  }

  /**
   * Returns a single message or thread based on parameters
   *
   * @param int $mid
   *   Id of the message or thread to retrieve
   *
   * @param string $type
   *  Optional string specifying whether to retrieve thread or msg;
   *  if not passed, the API will default to type = 'thread'
   *
   * @param string $fields
   *   Optional comma seperated list of fields to return.
   */
  public function messagesGetMessageOrThread($id, $type = NULL, $fields = NULL) { // @todo type = null or thread?
    //compile path
    $path = 'messages/'.$id;
    $query = array();
    if ($type){
      $query['type'] = $type;
    }
    if ($fields){
      $query['fields'] = $fields;
    }
    return $this->get($path, $query);
  }

  /**
   * List messages based on parameters
   *
   * @param string $box
   *  which box of messages to retrieve ('list' retrieves all, 'inbox' or 'sent')
   *
   * @param string $fields
   *   Optional comma seperated list of fields to return.
   *
   * @param int $limit
   *  how many messages to retrieve per page
   *
   * @param int $page
   *  Page of items to return, up to 20 per page.
   */
  public function messagesGetBox($box = NULL, $fields = NULL, $limit = NULL, $page = NULL) { // @todo type = null or thread?
    //compile path
    $path = 'messages';
    $parameters = array();
    if ($box){
      $parameters['box'] = $box;
    }
    return $this->index($path, $parameters, $fields, $page, $limit);
  }

  /**
   * Creates a message or thread by parameters
   *
   * @param int $thread_id
   *  if this is a reply to a current thread, pass the thread id, but not recipients or subject
   *
   * @param string $recipients
   *  Comma separated string of recipient UIDs
   *  pass only if this is a new message
   *
   * @param string $subject
   *  Message subject.  Pass only if this is new message
   *
   * @param string $body
   *  Message body.  always required
   */
  public function messagesCreateMessageOrThread($thread_id = NULL, $recipients = NULL, $subject = NULL, $body) {
    //compile path
    $path = 'messages';
    $params = array(
      'body' => $body,
    );
    if ($thread_id){
      $params['thread_id'] = $thread_id;
    }
    if ($recipients){
      $params['recipients'] = $recipients;
    }
    if ($subject){
      $params['subject'] = $subject;
    }
    return $this->post($path, $params);
  }

  /**
   * Updates the read status of message or thread
   *
   * @param int $id
   *   Id of the message or thread
   *
   * @param int $status
   *  1 for new, 0 for read
   *
   * @param string $type
   *  Optional string specifying whether thread or msg;
   *  if not passed, the API will default to type = 'thread'
   */
  public function messagesUpdateMessageOrThread($id, $status, $type = NULL) {
    //compile path
    $path = 'messages/'.$id;
    $params = array(
      'status' => $status,
    );
    if ($type){
      $params['type'] = $type;
    }
    return $this->put($path, $params);
  }

  /**
   * Returns a single message or thread based on parameters
   *
   * @param int $id
   *   Id of the message or thread to delete
   *
   * @param string $type
   *  Optional string specifying whether to retrieve thread or msg;
   *  if not passed, the API will default to type = 'thread'
   */
  public function messagesDeleteMessageOrThread($id, $type = NULL) {
    //compile path
    $path = 'messages/'.$id;
    $query = array();
    if ($type){
      $query['type'] = $type;
    }
    return $this->delete($path, $query);
  }


  /* ----- End Drupal Services endpoints ---- */

  /* ----- @todo - Start Functions needing refactoring ---- */
  /**
   * get nid_vals
   *
   * @deprecated
   */
  public function get_nid_vals() {
    var_dump(self::$nid_vals);
    return self::$nid_vals;
  }

  /* if debug set then show connect string
   */

  /**
   * if debug set then show response code and possibly response body
   *
   * @deprecated
   */
  private function process_response($get_cookie = 0,$get_sess = 0) {
    $this->responseCode = $this->rest->responseCode;
    //  $j =$this->rest->getResponse();
    //  var_dump($j);
    //  $this->output = json_decode($j);
    $this->output = json_decode($this->rest->getResponse());
    if ($get_cookie) {
      $this->cookie = $this->rest->getResponseCookies();
    }
    if ( ($this->debug >=5) || $this->responseCode != 200 ) {
      echo "  respCode:".$this->responseCode."\n";
      if ($this->responseCode != 200 ) {
        echo "***expected 200 here is body received:\n";
        var_dump($this->output);
        if ($this->debug >=10 ) {
          echo "***** REQ sent:\n";
          var_dump($this->rest);
        }
        echo "***** REQ InPUTS:\n";
        var_dump($this->rest->inputs);
        echo "***** \n";
        // if was path conflict, find the match and remove it if -x is set
        //

        exit(1);
      }
    }
    if($get_sess) {
      $this->sess = $this->output->sessid;
      $this->session_name = $this->output->session_name;
      if ($this->debug) {
        echo "sess:".$this->sess.":session_name:".$this->session_name."\n";
      }
    }

    //set cookie for any futaure rest communication
    $this->rest->addSessCookie($this->sess,$this->session_name);

    if ($this->debug >=10) {
      echo "output:";
      //print_r($this->output);
      var_dump($this->output);
    }

  }

  /**
   * Login as user to REST server
   *
   * @deprecated
   */
  public function rest_login() {
    /*
     global $inputs;
     global $apci_proto;
     global $sess;
     */
    $url = $this->proto.$this->host."/api/rest/user/login/";

    $inputs = array();
    $inputs["username"] = $this->user_name;
    $inputs["password"] = $this->password;

    $this->rest->createRequest("$url","POST",$inputs);
    $this->rest->addHeader("Cache-Control",'no-cache, must-revalidate, post-check=0, pre-check=0');
    $this->rest->sendRequest();
    $this->process_response (1,1);
    //  $user = $this->output->user;
    //  $name = $user->name;

  }


  /**
   * GET node data for $nid
   *
   * @deprecated
   */
  public function get_node_data ($nid) {

    $url = $this->proto.$this->host."/api/rest/node/".$nid;

    $this->rest->createRequest("$url","GET");
    $this->rest->addHeader("Cache-Control",'no-cache, must-revalidate, post-check=0, pre-check=0');
    $this->rest->addSessCookie($this->sess,$this->session_name);
    $this->rest->sendRequest();
    //  echo "GET NODE DATA\n";
    $this->process_response ();
    return ($this->output);
  }

  //////////
  /**
   * Setup book mapping both nids and mlids
   *
   * @deprecated
   */
  public function book_outline_mapping ($src_nodedata,$src_apcirest,&$inputs) {
    /// passed src_apcirest  and acting upon $dest


    if ($this->type == "book") {

      if (! $this->title) { // single book to fix
        $src_bid_book = $src_nodedata->book->bid; //bid
        if ($src_bid_book) {
          //       echo "book bid:$src_bid_book\n";

          // need to know mapping for if book page has a book-id (part of a book)
          // lookup by title of src book bid
          $dest_bid_title =$src_apcirest->map_vals->nid_vals[$src_bid_book]->title;
           echo "book bid TITLE:$dest_bid_title\n";
          $dest_bid = $this->map_vals->title_vals[$dest_bid_title]->nid;

          if ($dest_bid) {
            //lookup atitle for bid_book (from src)
            //echo "SRC BID BOOK $src_bid_book\n";
            //	  $src_bid_book_data = $src_apcirest->get_node_data($src_bid_book);
	    // TODO need to ensure dest_bid is really a book and make it one if it isn't
	    // had this happen with USER GUIDE

          }
          else {
            // no local bid book node found, will need it created

            // needs to be set to 'new' if this is a new node creation and book is it'self otherwise must create book and set to nid of that created.
            echo "dest bid title:$dest_bid_title, source node title:".$src_nodedata->title."\n";

            if ($dest_bid_title == $src_nodedata->title) {
              $dest_bid = 'new';
            }
            else { // create that book and set our vals to have it's data
              echo "create that book\n";
              $mess = "NEW-BID-CREATE(".$this->type."): MASTER BID:$src_bid_book";
              $mess .= " TITLE '".$src_nodedata->title."' ";
              echo $mess."\n";
              watchdog('apcipage_sync',$mess,array(),WATCHDOG_NOTICE);
              $src_nodedata_bid = $src_apcirest->get_node_data ($src_bid_book);
              $dest_bid = $this->create_node($src_nodedata_bid,$src_apcirest);
              // but also have to fix map_vals to hold same new bid just created.
            }
          }

        }
        else { // dont' know bid mapping
          // could be book with bad bid data or 0 as had on content.
          //echo "ERROR src bid not found for bid=$src_bid_book , nid=".$src_nodedata->nid."\n";
          echo "NO src bid found for nid=".$src_nodedata->nid."\n";
          $dest_bid = 0;
        }

        echo "GOT:book mapping (".$src_nodedata->nid.")".$src_bid_book."->".$dest_bid."\n";

        // need to set what bid is used since have a source bid based on map on des
        $inputs['book'][bid]  = $dest_bid;
        if (isset($src_nodedata->book->weight)) {
          $inputs['book'][weight] = $src_nodedata->book->weight;
        }
	else {
	   echo "Missing book weightfor src_nodedata".$src_nodedata->nid."\n";
        }

        $src_plid =$src_nodedata->book->plid;
        if ($src_plid && ($dest_bid != "new")) {
          $dest_plid_title = $src_apcirest->map_vals->mlid_vals[$src_plid]->title;
          echo "plid $src_plid with TITLE:$dest_plid_title\n";
          $dest_plid = $this->map_vals->title_vals[$dest_plid_title]->book->mlid;
          if ($dest_plid) {
            //cool dest plid is set
            //echo "dest plid is $dest_plid\n";
          }
          else { // no dest plid must create it
	    echo "\n!!NO dest_plid for title:$dest_plid_title\n\n";
            /*
            echo "$dest_bid is that\n";
            echo "create that plid\n";
            $mess = "NEW-PLID-CREATE(".$this->type."): MASTER PLID:$src_plid";
            $mess .= " TITLE '".$dest_plid_title."' ";
            echo $mess."\n";
            watchdog('apcipage_sync',$mess,array(),WATCHDOG_NOTICE);
            $src_nodedata_plid = $src_apcirest->get_node_data ($src_plid);
            $dest_plid = $this->create_node($src_nodedata_plid,$src_apcirest);
            */
          }
          echo "  GOT:plid mapping (".$src_nodedata->nid.")".$src_plid."->".$dest_plid."\n";
        }

        if (!$dest_plid ) {
          $dest_plid = 0;
        }
        if ($dest_bid != "new" && ($dest_plid != 0)) {
          $inputs['book'][plid] = $dest_plid;
        }

      } //single title
      else { // ignore plid and bid stuff
        /* ensure bid is ok, and don't mess with plid */

      }

    } //book type

  } // book outline mapping

  //////////
  /**
   *  CREATE NODE on DST based on src_nodedata
   *
   * @deprecated
   */
  public function  update_node ( $src_nodedata,$nid,$src_apcirest) {


/// dump src_node_data on update

    $url = $this->proto.$this->host."/api/rest/node/".$nid;

    //echo "CREATE NODE\n";
    $values_to_set = array ('title','body','path','type','promote','comment','format','revision_uid','revision_timestamp',sticky,status);
    // setup inputs for use on node creation

    foreach ($values_to_set as $val) {
      $inputs[$val] = $src_nodedata->$val;
    }
    $inputs["uid"]   = 1;
    $inputs["name"]  = "admin";

    $node_data = array();

    $this->book_outline_mapping ($src_nodedata,$src_apcirest,$inputs);
    $node_data['node'] = $inputs;
    $this->rest->createRequest("$url","PUT",$node_data);
    //  echo "sess is ".$this->sess."\n";
    $this->rest->addSessCookie($this->sess,$this->session_name);
    $this->rest->sendRequest();
    $this->process_response ();

  }


  //////////
  /**
   *  CREATE NODE on DST based on src_nodedata
   *
   * @deprecated
   */
  public function  create_node($src_nodedata,$src_apcirest) {

    $url = $this->proto.$this->host."/api/rest/node";

    //echo "CREATE NODE\n";
    $values_to_set = array ('title','body','path','type','promote','comment','format','sticky','status');
    // setup inputs for use on node creation
    $inputs = array();
    foreach ($values_to_set as $val) {
      //    echo "set $val\n";
      $inputs[$val] = $src_nodedata->$val;
    }
    $inputs["uid"]   = 1;
    $inputs["name"]  = "admin";

    // book outline
    $this->book_outline_mapping ($src_nodedata,$src_apcirest,$inputs);

    //post this node
    //echo "CREATE BOOK\n";
    //var_dump ($inputs);

    $node_data = array ();
    $node_data['node'] = $inputs;
    $this->rest->createRequest("$url","POST",$node_data);
    $this->rest->addSessCookie($this->sess,$this->session_name);

    $this->rest->sendRequest();
    $this->process_response ();

    // was a create node so TBD tell us what create NID was..
    // var_dump($this->output);
    $create_nid = $this->output->nid;

    // ensure $create_nid is a number or we had a create failure..
    if (!$create_nid || !is_numeric($create_nid)){
      echo "NODE CREATE FAILURE FOR SRC NID:".$src_nodedata->nid;
      echo " TYPE:".$src_nodedata->type;
      echo " TITLE:".$src_nodedata->title."\n";
      //bail as create nid failed
      exit(1);
    }

    // get the info and populate it
    $this->map_vals->nid_vals[$create_nid]->title = $src_nodedata->title;
    $this->map_vals->nid_vals[$create_nid]->path  = $src_nodedata->path;
    $this->map_vals->title_vals[$src_nodedata->title]->nid = $create_nid;

    /* must be from dest view */
    $dest_bid  = $inputs['book[bid]'];
    /* must obtain from nid just created */
    $dest_mlid_data = $this->get_node_data($create_nid);
    $dest_mlid =  $dest_mlid_data->book->mlid;

    $this->map_vals->nid_vals[$create_nid]->book->bid = $dest_bid;
    $this->map_vals->nid_vals[$create_nid]->book->mlid=$dest_mlid;
    $this->map_vals->title_vals[$src_nodedata->title]->book->bid =$dest_bid;
    $this->map_vals->title_vals[$src_nodedata->title]->book->mlid =$dest_mlid;
    $this->map_vals->mlid_vals[$dest_mlid]->nid    = $create_nid;
    $this->map_vals->mlid_vals[$dest_mlid]->title  = $dest_mlid_data->title;

    //var_dump($this->map_vals);

    return($create_nid);

  }


  /**
   * GET list of nodes that are of type='apcipage'
   *
   * @deprecated
   */
  public function get_apcipages($g_host,$g_rest) {

    // clear those to populate
    self::$nid_vals = array ();
    self::$title_vals = array ();
    self::$mlid_vals = array ();

    // must do this till get empty page, as could be more than 1 page
    //
    $the_nids = array();

    $page=0;
    $empty_page = 0;
    $i=0;
    while(!$empty_page) {

      $url = $this->proto.$this->host."/api/rest/node?parameters[type]=".$this->type;

      if ($this->title != "") {
        $url .= "&title=".rawurlencode($this->title);
        $empty_page = 1; // stop loop after this title
      }
      else {
        $url .="&page=".$page;
      }

      $this->rest->createRequest("$url","GET");
      $this->rest->addSessCookie($this->sess,$this->session_name);
      $this->rest->sendRequest();
      echo "-----GET (".$g_host.") APCIPAGE(s) of type ".$this->type."--------\n";
      $this->process_response ();

      //  var_dump($this->output);

      printf('%-4s|%-8s|','CNT','NID');
      if ($this->type == 'book') {
        printf('%-8s|%-8s|','BID','MLID');
      }
      printf('%-20s|','TITLE');
      printf("%-20s|\n",'PATH');
      printf("------------------------------------------------------------------\n");
      $old_i = $i;
      foreach ($this->output as $pg ) {
        // save nid and title
        if ($pg->nid) {
          $i++;
          $the_nids[] = $pg->nid;
          $nd_data = $this->get_node_data($pg->nid);
          if ($this->type == 'book') {
            self::$nid_vals[$pg->nid]->book->bid = $nd_data->book->bid;
            self::$nid_vals[$pg->nid]->book->mlid= $nd_data->book->mlid;
            self::$title_vals[$nd_data->title]->book->bid = $nd_data->book->bid;
            self::$title_vals[$nd_data->title]->book->mlid= $nd_data->book->mlid;
            self::$mlid_vals[$nd_data->book->mlid]->nid = $pg->nid;
            self::$mlid_vals[$nd_data->book->mlid]->title = $nd_data->title;
            //keep track of bids and plids
            if (!in_array($nd_data->book->bid,$this->bids)) {
              $this->bids[] = $nd_data->book->bid;
            }
            if ($nd_data->book->plid && !in_array($nd_data->book->plid,$this->plids)) {
              $this->plids[] = $nd_data->book->plid;
            }
          }
          self::$nid_vals[$pg->nid]->path  = $nd_data->path;
          self::$nid_vals[$pg->nid]->title = $nd_data->title;
          self::$title_vals[$nd_data->title]->path  = $nd_data->path;
          self::$title_vals[$nd_data->title]->nid = $pg->nid;
          // print table
          printf('%-4d|%-8d|',$i,$pg->nid);
          if ($this->type == 'book') {
            printf('%-8d|%-8d|',$nd_data->book->bid,$nd_data->book->mlid);
          }
          printf('%-20s|',$nd_data->title);
          printf("%-20s|\n",$nd_data->path);
        }
      }

      if ($old_i == $i) {
        $empty_page = 1;
      }
      $page++;

    }

    variable_set('apcipage_nodes',$the_nids);
    watchdog('apcipage_sync','Successfully retrieved '.$this->type.' node list from master server.',array(),WATCHDOG_NOTICE);

    $the_vals->nid_vals   =self::$nid_vals;
    $the_vals->title_vals =self::$title_vals;
    $the_vals->mlid_vals  =self::$mlid_vals;
    $this->map_vals = $the_vals;
    return ($the_vals);

  }

  /**
   * SHOW BIDS and PLIDS for data
   *
   * @deprecated
   */
  public function show_bids_plids () {
    // show bids and plids
    print ('BIDS:');
    foreach ($this->bids as $bid) {
      print "$bid,";
    }
    print "\n";
    // show bids and plids
    print ('PLIDS:');
    foreach ($this->plids as $plid) {
      print "$plid,";
    }
    print "\n";

  }

  /**
   * Get each page of data and sync it on this machine
   *
   * @deprecated
   */
  public function apcipage_sync_update_by_rest($src_apcirest) {

    //  echo "get those nodes\n";
    //  $node_ids = variable_get('apcipage_nodes',0);

    /// maybe should ensure bids,plids are all set 1st?
    // other way is to ensure they are in place when hit them

    echo "----Get each apcipage FULL Details and duplicate them\n";

    //foreach apcipage check and get data if already have that page.
    // passed src, and self,this is dest
    $k=0;
    foreach ($src_apcirest->map_vals->nid_vals as $nid =>$nid_val) {
      $k++;
      $src_nodedata = $src_apcirest->get_node_data ($nid);
      if ($src_nodedata) {

        // get matching node on dest machine if any
        // match up by title
        $mess = "$k:SYNC";
        //       echo "dst_nid:".self::$title_vals[$src_nodedata->title]->nid."\n";
        //       var_dump($this->map_vals->title_vals);

        if ($this->map_vals->title_vals[$src_nodedata->title]) {
          $dst_nid = $this->map_vals->title_vals[$src_nodedata->title]->nid;
          printf("NID:%d -> %d:%s \n",$nid,$dst_nid,$src_nodedata->title);
          $dst_node->nid = $dst_nid;
          $mess .= "-UPDATE(".$this->type."): MASTER NID:$nid, THIS NID:".$dst_node->nid;
          $mess .= " TITLE '".$src_nodedata->title."' ";
          echo $mess."\n";
          watchdog('apcipage_sync',$mess,array(),WATCHDOG_NOTICE);
          $this->update_node($src_nodedata,$dst_node->nid,$src_apcirest);
        }
        else { // create new node
          $mess .= "-CREATE(".$this->type."): MASTER NID:$nid";
          $mess .= " TITLE '".$src_nodedata->title."' ";
          echo $mess."\n";
          watchdog('apcipage_sync',$mess,array(),WATCHDOG_NOTICE);
          $this->create_node($src_nodedata,$src_apcirest);
        }
        if ($apci_debug) {
          print_r($node);
        }


      }
    }
  }

  /**
   * GET node data for node of certain type and title
   *
   * @deprecated
   */
  public function get_node_data_by_title ($title) {

    $url = $this->proto.$this->host."/api/rest/node?parameters[type]=".$this->type."&parameters[title]=".rawurlencode($title);

    $this->rest->createRequest($url,"GET");
    $this->rest->addHeader("Cache-Control",'no-cache, must-revalidate, post-check=0, pre-check=0');
    $this->rest->addSessCookie($this->sess,$this->session_name);
    $this->rest->sendRequest();
    $this->process_response ();
    //  echo "got node data now\n";
    $the_nid = $this->output[0]->nid;
    //  echo "get node data now for ".$the_nid."\n";
    if ($the_nid) {
      $nid_data = $this->get_node_data ($the_nid);
    }
    else {
      echo "  COULDN'T get SRC data for title:*$title*\n";
    }

    return(  $nid_data);
  }

  /**
   * Get each page of data and sync it on this machine
   *
   * @deprecated
   */
  public function apcipage_sync_update_by_db($src) {

    echo "----Get each  apcipage and duplicate them\n";

    // loop through each src one
    //foreach apcipage check and get data if already have that page.
    foreach ($nid_vals as $nd) {

      $nodedata = get_node_data ($this->host,$this->rest,$nd->nid);
      if ($nodedata) {
        //       echo "SYNC EXISTING NODE '".$nodedata->title."' : MASTER NID:$nd->nid, THIS NID:".$nodedata->nid." '".$nodedata->title."'\n";
        $node = "";

        // get matching node on this machine if any
        $result = db_fetch_array(db_query('SELECT n.nid, n.title, n.type , n.vid FROM {node} n WHERE n.title = "%s" AND n.type= "%s"', $nodedata->title, $this->type));
        //print_r($result);

        $mess = "SYNC ";
        if ($result && $result['nid']) {
          $node->nid = $result['nid'];
          $node->vid = $result['vid'];
          $mess .= "EXISTING";
        }
        $mess .= ": MASTER NID:$nd->nid, THIS NID:".$node->nid;
        $mess .= " TITLE '".$nodedata->title."' ";
        echo $mess."\n";
        watchdog('apcipage_sync',$mess,array(),WATCHDOG_NOTICE);
        $node->type   = $nodedata->type;
        $node->uid    = 1;
        $node->status = $nodedata->status;
        $node->created = $nodedata->created;
        $node->changed = $nodedata->changed;
        $node->comment = $nodedata->comment;
        $node->promote = $nodedata->promote;
        $node->sticky   = $nodedata->sticky;
        $node->status   = $nodedata->status;
        $node->moderate = $nodedata->moderate;
        $node->sticky   = $nodedata->sticky;
        $node->tnid     = $nodedata->tnid;
        $node->translate = $nodedata->translate;
        $node->title  = $nodedata->title;
        $node->body   = $nodedata->body;
        $node->teaser = $nodedata->teaser;
        $node->format = $nodedata->format;
        $node->name   = $nodedata->name;
        $node->data   = $nodedata->data;
        $node->path   = $nodedata->path;
        if ($this->debug) {
          print_r($node);
        }

        // create or update the node
        node_save($node);
        unset($node);

      }
    }
  }
  /* ----- End functions needing refactoring ---- */
} // end of class

/* ----- Begin helpers ----- */
/**
 * Prepare recieved node to be returned as an update for creation.
 *
 * @param stdClass $node
 * @return array
 */
function prepare_node_obj($node) {
  // Remove fields that break update.
  $filter_fields = array(
    'created',
    'changed',
    'data',
  );

  foreach ($filter_fields as $field) {
    if (isset($node->{$field})) {
      unset($node->{$field});
    }
  }

  return array_filter(object_to_array($node));
}

/**
 * Helper function to recursively convert objects to arrays.
 */
function object_to_array($convert) {
  if (is_object($convert)) {
    $convert = (array)$convert;
  }
  foreach ($convert as &$val) {
    if (is_array($val) || is_object($val)) {
      $val = object_to_array($val);
    }
  }
  return $convert;
}
/* ----- End helpers ----- */
