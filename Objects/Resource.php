<?php
namespace AllPlayers\Objects;

use DateTime;
use stdClass;

/**
 * Defines standard Resource fields.
 */
abstract class Resource extends stdClass {
  /**
   * @var string
   */
  public $title;

  /**
   * @var array
   */
  public $location;

  /**
   * @var array
   */
  public $groups;

  /**
   * @var array
   */
  public $availability;
}
