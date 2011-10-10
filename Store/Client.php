<?php
namespace AllPlayers\Store;

class Client extends \AllPlayers\Component\HttpClient{
  // @todo - This isn't configurable upstream.
  const ENDPOINT = '/api/rest/v1/';

  /**
   * Default AllPlayers.com Store URL.
   *
   * @var string
   */
  public $base_url = 'https://store.allplayers.com';

  /**
   * @musthave
   * Link to users cart.
   */
  function usersCartUrl() {
    return $this->base_url . '/cart';
  }

  /**
   * @musthave
   * Link to users orders.
   */
  function usersOrdersUrl() {
    return $this->base_url . '/orders';
  }
  /**
   * @musthave
   * Link to users bills.
   */
  function usersBillsUrl() {
    return $this->base_url . '/bills';
  }

  /**
   * @nicetohave
   * Line items in the cart services users cart.
   *
   * @return Array
   *   Array of cart line item objects.
   */
  function usersCartIndex() {
    return $this->index('users/mycart');
  }

  /**
   * @musthave
   * Add items to the services users cart.
   *
   * @return bool
   *   TRUE if succesfully added.
   */
  function usersCartAdd($product_uuid) {
    return $this->post('users/mycart/add', array('product_uuid' => $product_uuid));
  }

  /**
   * @musthave
   * Link to group store.
   *
   * @param string $uuid
   */
  function groupStoreUrl($uuid) {
    return $this->base_url . '/group_store/' . $uuid;
  }

  /**
   * @musthave
   * @todo - Get information about a group store if it exists.
   *
   * @param string $uuid
   *   Group UUID string.
   */
  function groupStoreGet($uuid) {
    return $this->get('group_stores/' . $uuid);
  }

  /**
   * @musthave
   * @todo - Initialize and enable a group store now.
   * @todo - This will require different privileges? Or should we just expect the current user to have that?
   *
   * @param string $uuid
   *   Group UUID string.
   */
  function groupStoreEnable($uuid) {
    return $this->put('group_stores/' . $uuid . '/enable');
  }

  /**
   * @musthave
   * @todo - List group products, optionally by type.
   *
   * @param unknown_type $group_uuid
   * @param unknown_type $type
   * @return Array
   *   Array of product objects.
   */
  function groupStoreProductsIndex($group_uuid, $type = NULL) {
    $params = ($type) ? array('type' => $type) : array();
    return $this->index('group_stores/' . $group_uuid . '/products', $params);
  }

  /**
   * @nicetohave
   * @param unknown_type $uuid
   */
  function productGet($uuid) {
    return $this->get('products/' . $uuid);
  }
}
