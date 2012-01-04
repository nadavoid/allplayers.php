<?php
namespace AllPlayers\Objects;

use DateTime;

/**
 * Defines standard user fields.
 */
abstract class User extends stdClass {
  /**
   * @var string
   */
  public $email;

  /**
   * @var string
   */
  public $firstname;

  /**
   * @var string
   */
  public $lastname;

  /**
   * @var string
   */
  public $gender;

  /**
   * @var DateTime
   */
  public $birthdate;
}
