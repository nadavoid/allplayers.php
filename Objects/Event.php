<?php
namespace AllPlayers\Objects;

use stdClass;

/**
 * Defines standard Event fields.
 */
abstract class Event extends stdClass {

  /**
   * @var array
   */
  public $groups;

  /**
   * @var string
   */
  public $title;

  /**
   * @var string
   */
  public $description;

  /**
   * @var array
   */
  public $date_time;

  /**
   * @var string
   */
  public $category;

  /**
   * @var array
   */
  public $resource_ids;

  /**
   * @var array
   */
  public $competitors;

  /**
   * @var string
   */
  public $published;

  /**
   * @var string
   */
  public $external_id;
}
