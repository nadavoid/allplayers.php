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
   * @param string $user_uuid
   *   User UUID string.
   *
   * @return Array
   *   Array of cart line item objects.
   */
  function usersCartIndex($user_uuid) {
    return $this->index('users/' . $user_uuid . '/cart');
  }

  /**
   * @musthave
   * Add items to the services users cart.
   *
   * @param string $user_uuid
   *   User UUID string.
   * @param string $product_uuid
   *   Product UUID string.
   *
   * @return bool
   *   TRUE if succesfully added.
   */
  function usersCartAdd($user_uuid, $product_uuid, $for_user_uuid = NULL) {
    return $this->post('users/' . $user_uuid . '/add_to_cart', array('product_uuid' => $product_uuid, 'for_user_uuid' => $for_user_uuid));
  }

  /**
   * @musthave
   * Link to group store.
   *
   * @param string $uuid
   */
  function groupStoreUrl($uuid) {
    return $this->base_url . '/group_store/uuid/' . $uuid;
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

  /**
   * @musthave
   * Link to product base path.
   *
   * @param string $uuid
   */
  function productUrl($uuid) {
    return $this->base_url . '/product/uuid/' . $uuid;
  }
}
