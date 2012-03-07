<?php
namespace AllPlayers\Objects;

use stdClass;
use DateTime;

/**
 * Defines standard APDate fields.
 */
abstract class APDate extends stdClass {

  /**
   * @var string
   */
  public $start;

  /**
   * @var string
   */
  public $end;
}
