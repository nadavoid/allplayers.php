<?php
namespace AllPlayers\Objects;

use stdClass;

/**
 * AllPlayers webform.
 */
abstract class Webform extends stdClass {

  // Setup some constant field states.
  const DO_NOT_COLLECT = 0;
  const OPTIONAL = 1;
  const REQUIRED = 2;

  /**
   * @var string
   */
  public $title;

  /**
   * @var array
   */
  public $groups;

  /**
   * @var array
   */
  public $fields = array(
    'firstname' => Webform::DO_NOT_COLLECT,
    'lastname' => Webform::DO_NOT_COLLECT,
    'middle-name' => Webform::DO_NOT_COLLECT,
    'nickname' => Webform::DO_NOT_COLLECT,
    'address' => Webform::DO_NOT_COLLECT,
    'email' => Webform::DO_NOT_COLLECT,
    'household' => Webform::DO_NOT_COLLECT,
    'siblings' => Webform::DO_NOT_COLLECT,
    'lives-with' => Webform::DO_NOT_COLLECT,
    'computer-access' => Webform::DO_NOT_COLLECT,
    'birth-date' => Webform::DO_NOT_COLLECT,
    'user-gender' => Webform::DO_NOT_COLLECT,
    'phone' => Webform::DO_NOT_COLLECT,
    'phone-cell' => Webform::DO_NOT_COLLECT,
    'height' => Webform::DO_NOT_COLLECT,
    'weight' => Webform::DO_NOT_COLLECT,
    'school-grade' => Webform::DO_NOT_COLLECT,
    'school' => Webform::DO_NOT_COLLECT,
    'ethnicity' => Webform::DO_NOT_COLLECT,
    'preferred-contact-via' => Webform::DO_NOT_COLLECT,
    'pickup-child' => Webform::DO_NOT_COLLECT,
    'pickup-child-passwd' => Webform::DO_NOT_COLLECT
    /** @todo:  Fill the rest of the fields out. */
  );

  /**
   *  Constructs new self from passed parameters
   *
   * @param string $uuid
   *   Webform uuid.
   * @param string $title
   *   Webform title.
   * @param array $groups
   *   Webform groups.
   * @param array $fields
   *   Webform fields
   */
  public function __construct($title, $groups = array(), $fields = array()) {
    $this->title = $title;
    $this->groups = array();
    if ($groups) {
      foreach ($groups as $group) {
        $this->addGroup($group);
      }
    }
    if ($fields) {
      $this->fields = array_merge($this->fields, $fields);
    }
  }

  /**
   * Add the group to the groups array.
   */
  public function addGroup($group) {
    if ($group) {
      $this->groups[] = is_object($group) ? $group->uuid : $group;
    }
  }
}
