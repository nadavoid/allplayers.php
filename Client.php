<?php
namespace AllPlayers;

use AllPlayers\Component\HttpClient;

use ErrorException;
use Log;

/**
 * Methods for interacting with the main AllPlayers application API.
 */
class Client extends HttpClient {

  /**
   * @param string $url
   *   e.g. https://www.allplayers.com
   * @param Log $logger
   *   (optional)
   */
  public function __construct($base_url, Log $logger = NULL) {
    parent::__construct($base_url . '/api/v1/rest', $logger);
  }

  /**
   * Fetch a user by uuid.
   *
   * @param $uuid
   * @return object
   *   user object.
   */
  public function userGetUser($uuid) {
    return $this->get("users/$uuid");
  }

  /**
   * Get a list of users based on fields passed in
   *
   * @param array $field
   *   parameters to pass as a query string while searching for users.
   *
   * @return array
   *   This is an array of user objects
   */
   public function usersIndex($fields = array()) {
     return $this->get("users", $fields);
   }

  /**
   * Update a user
   *
   * @param $firstname
   * @param $lastname
   * @param $email
   * @param $gender
   * @param $birthday
   * @param $password
   * @param $last_modified
   * @return object
   *   user object
   */
  public function userUpdateUser($uuid, $firstname, $lastname, $email, $gender, $birthday, $password = NULL, $last_modified) {
    $params = array(
      'firstname' => $firstname,
      'lastname' => $lastname,
      'email' => $email,
      'gender' => $gender,
      'birthday' => $birthday,
      'password' => $password,
      'last_modified' => $last_modified,
    );
    return $this->put("users/".$uuid, array_filter($params));
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
  public function userCreateUser($firstname, $lastname, $email, $gender, $birthday, $password = NULL) {
    $userData = array(
      'firstname' => $firstname,
      'lastname'  => $lastname,
      'email'     => $email,
      'gender'    => $gender,
      'birthday'  => $birthday,
      'password'  => $password
    );
    try {
      $ret = $this->post("users", array_filter($userData));
    }
    catch (ErrorException $e) {
      $messageJson = $this->rest->getResponse();
      $messageParts = json_decode($messageJson);
      if (!empty($messageParts->form_errors)) {
        //if there are form errors besides captcha
        throw $e;
      }

      // Retry if captcha error.
      // @todo - Create seperate Exception for captcha to simplify this code.
      if (isset($messageParts->captcha_error)) {
        $answer = $this->captchaSolve($messageParts->captcha_error->captcha_problem);
        $headers = array(
          'X-ALLPLAYERS-CAPTCHA-TOKEN'    => $messageParts->captcha_error->captcha_token,
          'X-ALLPLAYERS-CAPTCHA-SOLUTION' => $answer,
        );
        $ret = $this->post("users", array_filter($userData), $headers);
      }
    }
    return $ret;
  }

  /**
   * Solve a captcha
   *
   * @param string $problem
   *    Math captchas look like "7 + 4 = "
   * @return string
   *    The correct captcha answer
   */
  function captchaSolve($problem) {
    $parts = explode('+', $problem);
    $math1 = trim($parts[0]);
    $math2parts = explode('=', $parts[1]);
    $math2 = trim($math2parts[0]);
    $answer = $math1 + $math2;
    return (string) $answer;
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
   * @param int $pagesize
   *  how many results to return per page
   */
  public function userGetMyGroups($uuid, $fields = NULL, $page = 0, $pagesize = NULL) {
    //compile path
    $path = 'users/' . $uuid . '/groups';
    return $this->index($path, $parameters = NULL, $fields, $page, $pagesize);
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
   * @param int $pagesize
   *  how many results to return per page
   */
  public function userGetGroupmates($uuid, $fields = NULL, $page = 0, $pagesize = NULL) {
    //compile path
    $path = 'users/' . $uuid . '/groupmates';
    return $this->index($path, $parameters = NULL, $fields, $page, $pagesize);
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
   * @param int $pagesize
   *  how many results to return per page
   */
  public function userGetFriends($uuid, $fields = NULL, $page = 0, $pagesize = NULL) {
    //compile path
    $path = 'users/' . $uuid . '/friends';
    return $this->index($path, $parameters = NULL, $fields, $page, $pagesize);
  }

  /**
   * List user's friend requests based on parameters
   *
   * @param string $uuid
   *  user uuid
   */
  public function userGetFriendRequests($uuid) {
    //compile path
    $path = 'users/' . $uuid . '/friendrequests';
    return $this->index($path);
  }

  /**
   * Request to be a friend with a user
   *
   * @param string $uuid
   *  user uuid of requestee
   */
  public function userRequestFriend($uuid) {
    //compile path
    $path = 'users/' . $uuid . '/requestfriend';
    return $this->post($path);
  }

  /**
   * Approve a friendship request
   *
   * @param string $uuid
   *  user uuid of requestee
   *
   * @param int $request_id
   *  ID of friend request to approve
   */
  public function userApproveFriend($uuid, $request_id) {
    //compile path
    $path = 'users/' . $uuid . '/approvefriend/' . $request_id;
    return $this->post($path);
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
   * @param int $pagesize
   *  how many results to return per page
   */
  public function userGetEvents($uuid, $fields = NULL, $page = 0, $pagesize = NULL) {
    //compile path
    $path = 'users/' . $uuid . '/events';
    return $this->index($path, $parameters = NULL, $fields, $page, $pagesize);
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
   * @param int $pagesize
   *  how many results to return per page
   */
  public function userGetEventsUpcoming($uuid, $fields = NULL, $page = 0, $pagesize = NULL) {
    //compile path
    $path = 'users/' . $uuid . '/events/upcoming';
    return $this->index($path, $parameters = NULL, $fields, $page, $pagesize);
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
    $this->cookies = array();
    $this->session = NULL;
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
    $path = 'groups/' . $uuid;
    $query = array();
    if ($fields) {
      $query['fields'] = $fields;
    }
    return $this->get($path, $query);
  }

  /**
   * Create a group
   *
   * @param $title
   * @param $description
   * @param array $location
   *   Contains group location information in an array.
   *   $location['zip'] is required at minimum
   * @param array $category
   *   Contains group category in an array.
   *   $category[0] = 'sports'
   * @param array $optional_config
   *   Contains optional groups configuration. Possible keys
   *    group_type - what type of group is this
   *    web_address - groups web address. after www.allplayers.com/g/
   *    $status - active or inactive
   *    $groupmates_enabled - FALSE or TRUE
   *    $groups_above - array of parent groups
   * @return object
   *   user object
   */
  public function groupsCreateGroup($title, $description, $location, $category, $optional_config) {
    $params = array(
      'title' => $title,
      'description' => $description,
      'location' => $location,
      'category' => $category,
      'group_type' => $optional_config['group_type'],
      'web_address' => $optional_config['web_address'],
      'status' => $optional_config['status'],
      'groupmates_enabled' => $optional_config['groupmates_enabled'],
      'groups_above' => $optional_config['groups_above'],
    );
    return $this->post("groups", array_filter($params));
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
   * @param int $pagesize
   *  how many results to retrieve per page
   *
   * @param mixed $page
   *  what page of the results to call
   */
  public function groupsIndex($search = NULL, $zip = NULL, $search_distance = 10, $search_units = 'mile',
      $fields = NULL, $pagesize = NULL, $page = 0) {
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
    return $this->index($path, $parameters, $fields, $page, $pagesize);
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
   * @param int $pagesize
   *  how many results should come back per page
   */
  public function groupsGetMembers($uuid, $fields = NULL, $page = 0, $pagesize = NULL) {
    //compile path
    $path = 'groups/' . $uuid . '/members';
    return $this->index($path, $parameters = NULL, $fields, $page, $pagesize);
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
   * @param int $pagesize
   *  how many results should come back per page
   */
  public function groupsGetAlbums($uuid, $fields = NULL, $page = 0, $pagesize = NULL) {
    //compile path
    $path = 'groups/' . $uuid . '/albums';
    return $this->index($path, $parameters = NULL, $fields, $page, $pagesize);
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
   * @param int $pagesize
   *  how many results should come back per page
   */
  public function groupsGetPhotos($uuid, $fields = NULL, $page = 0, $pagesize = NULL) {
    //compile path
    $path = 'groups/' . $uuid . '/photos';
    return $this->index($path, $parameters = NULL, $fields, $page, $pagesize);
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
   * @param int $pagesize
   *  how many results should come back per page
   */
  public function groupsGetEvents($uuid, $fields = NULL, $page = 0, $pagesize = NULL) {
    //compile path
    $path = 'groups/' . $uuid . '/events';
    return $this->index($path, $parameters = NULL, $fields, $page, $pagesize);
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
   * @param int $pagesize
   *  how many results should come back per page
   */
  public function groupsGetEventsUpcoming($uuid, $fields = NULL, $page = 0, $pagesize = NULL) {
    //compile path
    $path = 'groups/' . $uuid . '/events/upcoming';
    return $this->index($path, $parameters = NULL, $fields, $page, $pagesize);
  }

  /**
   * List roles belonging to a group (group_uuid), optionally targeting a
   * specific user (uuid).
   *
   * @param string $group_uuid
   * @param string $uuid
   */
  public function groupsGetRoles($group_uuid, $uuid = NULL) {
    $path = 'groups/' . $group_uuid . '/roles';
    if (!empty($uuid)) {
      $path .= '/' . $uuid;
    }

    return $this->index($path);
  }

  /**
   * Join a user to a group
   *
   * @param group_uuid
   * @param user_uuid
   * @return object
   *   user object
   */
  public function groupsJoinUser($group_uuid, $user_uuid) {
    return $this->post('groups/' . $group_uuid . '/join/' . $user_uuid);
  }

  /**
   * Set a group's manager
   *
   * @param group_uuid
   * @param user_uuid
   * @return object
   *   user object
   */
  public function groupsSetManager($group_uuid, $user_uuid) {
    return $this->post('groups/' . $group_uuid . '/setmanager/' . $user_uuid);
  }

  /**
   * Create a notifier
   *
   * @param string $title
   *   The title of the notifier node.
   * @param string $body
   *   The body of the notifier, which will be displayed to users.
   * @param array $optional_config
   *   Contains optional notifier configuration. Possible keys
   *    $type - The type of notifier: error, warning, success, or info.
   *    $users - An array of usernames (not UID) that the notifier is directed to.
   *    $groups - An array of group NIDs that the notifier is directed to.
   *    $identifier - An identifier value to uniquely identify this notifier.
   *    $visibility - The visibility of the notifier: group members or anyone.
   *      Only applies to notifiers in group space.
   *    $global_notifier - Specifies if the notifier is global in either group
   *      or user space.
   *    $group_filter - Specifies if the notifier is directed to admins or
   *      non-admins. Only useful if the global notifier is set to group space.
   *
   * @return object
   *   Notifier node.
   */
  public function notifiersCreateNotifier($title, $body, $optional_config) {
    $params = array(
      'title' => $title,
      'body' => $body,
      'type' => isset($optional_config['type']) ? $optional_config['type'] : 'warning',
      'users' => isset($optional_config['users']) ? $optional_config['users'] : array(),
      'groups' => isset($optional_config['groups']) ? $optional_config['groups'] : array(),
      'identifier' => isset($optional_config['identifier']) ? $optional_config['identifier'] : '',
      'visibility' => isset($optional_config['visibility']) ? $optional_config['visibility'] : 'group members',
      'global_notifier' => isset($optional_config['global_notifier']) ? $optional_config['global_notifier'] : NULL,
      'group_filter' => isset($optional_config['group_filter']) ? $optional_config['group_filter'] : NULL,
    );
    return $this->post("notifier", array_filter($params));
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
   * @param int $pagesize
   *  how many results should come back per page
   */
  public function albumsGetPhotos($uuid, $fields = NULL, $page = 0, $pagesize = NULL) {
    //compile path
    $path = 'albums/' . $uuid . '/photos';
    return $this->index($path, $parameters = NULL, $fields, $page, $pagesize);
  }

  /**
   * Return a specific photo based on parameters
   *
   * @param int $id
   *  photo id that should be retrieved
   *
   * @param string $fields
   *  Optional comma separated list of fields to return.
   */
  public function photosGetPhoto($pid, $fields = NULL) {
    //compile path
    $path = 'photos/' . $pid;
    $query = array('fields' => $fields);
    return $this->get($path, $query);
  }

  /**
   * Create an event
   * @param array $groups
   *   A numerically keyed array of group uuids.
   *
   * @param $title
   *
   * @param $description
   *
   * @param array $date_time
   *   Contains event date information with all times in UTC
   *   $date_time['start'] and $date_time['end'] required at minimum
   *
   * @param string $category
   *   Contains event category
   *
   * @param array $resources
   *   a numerically keyed array of resource uuids
   *
   * @param array $competitors
   *   an array of competitors groups UUIDs, labels and scores. Example:
   * @code
   *   $competitors = array(
   *    'c964752e-eead-11e0-abff-080027706aa2' => array(
   *       'label' => 'Home Team',
   *       'score' => '35',
   *     ),
   *    'b0f67f02-6179-11e1-9932-b37b4e17875f' => array(
   *      'label' => 'Away Team'
   *      'score' => '30',
   *     ),
   *   );
   * @endcode
   *
   * @param string $published
   *   TRUE/FALSE whether event will be published.
   *
   * @param string $external_id
   *   An external ID to associate to a remote database
   *
   * @return object
   *   user object
   */
  public function eventsCreateEvent($groups, $title, $description, $date_time, $category = NULL, $resources = NULL, $competitors = NULL, $published = TRUE, $external_id = NULL) {
    $params = array(
      'groups' => $groups,
      'title' => $title,
      'description' => $description,
      'date_time' => $date_time,
      'category' => $category,
      'resources' => $resources,
      'competitors' => $competitors,
      'published' => $published,
      'external_id' => $external_id,
    );
    return $this->post("events", array_filter($params));
  }

  /**
   * Update an event
   * @param array $groups
   *   A numerically keyed array of group uuids.
   *
   * @param $title
   *
   * @param $description
   *
   * @param array $date_time
   *   Contains event date information with all times in UTC
   *   $date_time['start'] and $date_time['end'] required at minimum
   *
   * @param string $category
   *   Contains event category
   *
   * @param array $resources
   *   a numerically keyed array of resource uuids
   *
   * @param array $competitors
   *   an array of competitors groups UUIDs, labels and scores. Example:
   * @code
   *   $competitors = array(
   *    'c964752e-eead-11e0-abff-080027706aa2' => array(
   *       'label' => 'Home Team',
   *       'score' => '35',
   *     ),
   *    'b0f67f02-6179-11e1-9932-b37b4e17875f' => array(
   *      'label' => 'Away Team'
   *      'score' => '30',
   *     ),
   *   );
   * @endcode
   *
   * @param string $published
   *   TRUE/FALSE whether event will be published.
   *
   * @param string $external_id
   *   An external ID to associate to a remote database
   *
   * @return object
   *   user object
   */
  public function eventsUpdateEvent($event_uuid, $groups = NULL, $title = NULL, $description = NULL, $date_time = NULL, $category = NULL, $resources = NULL, $competitors = NULL, $published = TRUE, $external_id = NULL) {
    $params = array(
      'groups' => $groups,
      'title' => $title,
      'description' => $description,
      'date_time' => $date_time,
      'category' => $category,
      'resources' => $resources,
      'competitors' => $competitors,
      'published' => $published,
      'external_id' => $external_id,
    );
    return $this->put("events/" . $event_uuid, array_filter($params));
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
    $path = 'events/' . $eid;
    $query = array();
    if ($fields) {
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
    $path = 'resources/' . $uuid;
    $query = array();
    if ($fields) {
      $query['fields'] = $fields;
    }
    return $this->get($path, $query);
  }

  /**
   * Create a resource
   *
   * @param $groups
   *   An array of group_uuids this resource belongs to
   * @param $title
   *   Resource Title
   * @param $location
   *   $location array.  $location['zip'] required at minimum
   * @param $availability
   *   $array of the resource's availabilities.  If none passed, the resource is assumed
   *   to be always available
   * @param $external_id
   *   a 72 character string to associate to external data
   * @return object
   *   resource object
   */
  // @todo why is this taking a node?
  public function resourceCreate($groups, $title, $location, $availability = NULL, $external_id = NULL) {
    $params = array(
      'groups' => $groups,
      'title' => $title,
      'location' => $location,
      'availability' => $availability,
      'external_id' => $external_id,
    );
    return $this->post("resources", array_filter($params));
  }

  /**
   * Update a resource
   *
   * @param $uuid
   *   the UUID of the resource being updated
   * @param $groups
   *   An array of group_uuids this resource belongs to
   * @param $title
   *   Resource Title
   * @param $location
   *   $location array.  $location['zip'] required at minimum
   * @param $availability
   *   $array of the resource's availabilities.  If none passed, the resource is assumed
   *   to be always available
   * @param $external_id
   *   a 72 character string to associate to external data
   * @return object
   *   resource object
   */
  // @todo why is this taking a node?
  public function resourceUpdate($uuid, $groups = NULL, $title = NULL, $location = NULL, $availability = NULL, $external_id = NULL) {
    $params = array(
      'groups' => $groups,
      'title' => $title,
      'location' => $location,
      'availability' => $availability,
      'external_id' => $external_id,
    );
    return $this->put("resources/" . $uuid, array_filter($params));
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
  public function messagesGetMessageOrThread($id, $type = NULL, $fields = NULL) {
    // @todo type = null or thread?
    //compile path
    $path = 'messages/' . $id;
    $query = array();
    if ($type) {
      $query['type'] = $type;
    }
    if ($fields) {
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
   * @param int $pagesize
   *  how many messages to retrieve per page
   *
   * @param int $page
   *  Page of items to return, up to 20 per page.
   */
  public function messagesGetBox($box = NULL, $fields = NULL, $pagesize = NULL, $page = 0) {
    // @todo type = null or thread?
    //compile path
    $path = 'messages';
    $parameters = array();
    if ($box) {
      $parameters['box'] = $box;
    }
    return $this->index($path, $parameters, $fields, $page, $pagesize);
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
    $params = array('body' => $body,);
    if ($thread_id) {
      $params['thread_id'] = $thread_id;
    }
    if ($recipients) {
      $params['recipients'] = $recipients;
    }
    if ($subject) {
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
    $path = 'messages/' . $id;
    $params = array('status' => $status,);
    if ($type) {
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
    $path = 'messages/' . $id;
    $query = array();
    if ($type) {
      $query['type'] = $type;
    }
    return $this->delete($path, $query);
  }
}