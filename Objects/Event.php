<?php
namespace AllPlayers\Objects;

use stdClass;
use AllPlayers\Calendar\Vevent;

/**
 * Defines standard Event fields.
 */
class Event extends stdClass {
  /**
   * @var string
   */
  public $uuid;
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

  /*Public Functions*/

  public static function fromApi($api_data){
    if (!is_array($api_data)){
      $api_data = (array) $api_data;
    }
    $date_time = Vevent::fromApi($api_data['date_time']);
    $competitors = empty($api_data['competitors']) ? NULL : $api_data['competitors'];
    $external_id = empty($api_data['external_id']) ? NULL : $api_data['external_id'];
    // @todo resource_ids come back as classes which should really be resource properties of an
    // event.  However since we don't yet have a 'teaser' schema, it'll have to wait.
    $resource_ids = NULL;
    if (!empty($api_data['resource_ids'])){
      $resource_ids = array();
      foreach ($api_data['resource_ids'] as $rid){
        $resource_ids[] = $rid->uuid;
      }
    }
    return new self($api_data['uuid'], $api_data['groups'], $api_data['title'], $api_data['description'], $date_time,  $api_data['category'], $resource_ids, $competitors, $api_data['published'], $external_id);
  }

  public function __construct($uuid, $groups, $title, $description, $date_time, $category, $resource_ids, $competitors = NULL, $published = NULL, $external_id = NULL) {
    $this->uuid = $uuid;
    $this->groups = $groups;
    $this->title = $title;
    $this->description = $description;
    $this->date_time = $date_time;
    $this->category = $category;
    $this->resource_ids = $resource_ids;
    $this->competitors = $competitors;
    $this->published = $published;
    $this->external_id = $external_id;
  }
    /**
   * Compares two objects against each other
   *
   * @param $randomEvent
   *   object comparing to
   * @param $apiEvent
   *   object retrieved from API
   * @param array $date_settings
   *   Date settings that specified how this object was constructed
   *
   * @return array
   *   Returns a diff array
   */
  public function diff($otherEvent) {
    $accuracy = TRUE;
    // @todo camelcase
    $properties = get_object_vars($this);
    $simple_compare = array();
    $diff = array();
    foreach ($properties as $key => $prop) {
      if (is_string($prop) || is_bool($prop) || is_int($prop)) {
        $simple_compare[$key] = $prop;
      }
    }
    if ($otherEvent->category == 'game' || $otherEvent->category == 'scrimmage') {
      // There should be competitors here.
      // The title should be hijacked by the api and should not equal.
      if (empty($otherEvent->competitors) || !strpos($otherEvent->title, '@')) {
        $diff['competitors'] = 'competitors';
        $diff['title'] = $otherEvent->title;
      }
      else {
        // Check competitors
        $this_competitors = $this->competitors;
        $other_competitors = $otherEvent->competitors;
        foreach ($this_competitors as $r_comp) {
          $r_comp = (array) $r_comp;
          foreach ($other_competitors as $api_comp) {
            $api_comp = (array) $api_comp;
            $found = 0;
            if ($r_comp['uuid'] == $api_comp['uuid']) {
              $found = 1;
              // UUID matches, that means if there are labels or scores those should match too.
              if ((!empty($r_comp['label']) && $r_comp['label'] != $api_comp['label']) ||
                  (!empty($r_comp['score']) && $r_comp['score'] != $api_comp['score'])) {
                $found = 0;
              }
              //Break the loop.
              break;
            }
          // Check complete, check found and throw error by diff.
          if ($found == 0) {
            $diff['competitiors'][] = $r_comp['uuid'];
          }
        }
      }
      // Make sure to unset title from $simple_compare or it will fail.
      unset($simple_compare['title']);
    }
    foreach ($simple_compare as $name => $value) {
      if (empty($otherEvent->$name) || $otherEvent->$name != $value) {
        $diff[$name] = $value;
        break;
      }
    }
    if (!empty($this->groups)) {
      foreach ($this->groups as $group) {
        if (!in_array($group, $otherEvent->groups)) {
          $diff['groups'][] = 'groups';
          break;
        }
      }
    }
    else {
      $diff['groups'] = 'groups';
    }

    if (!empty($this->resource_ids)) {
      // Rebuild for comparison
      foreach ($this->resource_ids as $rid) {
        if (!in_array($rid, $otherEvent->resource_ids)) {
          $diff['resources'][] = $rid;
        }
      }
    }
    // Last but definitely not least, compare the dates.
    $diff_date = $this->date_time->diff($otherEvent->date_time);
    if (!empty($diff_date)) {
      $diff['date'] = $diff_date;
    }
    return $diff;
  }
}
