<?php
namespace AllPlayers\Store;

use AllPlayers\Component\HttpClient;

class Client extends HttpClient {
  // @todo - This isn't configurable upstream.
  const ENDPOINT = '/api/v1/rest';

  /**
   * Default AllPlayers.com Store URL.
   *
   * @var string
   */
  public $base_url = NULL;

  /**
   * @param string $base_url
   *   e.g. "https://store.mercury.dev.allplayers.com"
   * @param Log $logger
   *   (Optional)
   */
  public function __construct($base_url, Log $logger = NULL) {
    if (empty($base_url)) {
      throw new InvalidArgumentException('Invalid argument 1: base_url must be a base URL to the Store.');
    }
    $this->base_url = $base_url;
    parent::__construct($base_url . self::ENDPOINT, $logger);
  }

  /**
   * Returns the registration SKU.
   *
   * @param object $group_name The name of the group.
   * @param number $role_id The role ID for the registration.
   * @return string The SKU for the registration product.
   */
  public static function getRegistrationSKU($group_name, $role_id) {
    return strtolower(substr(preg_replace('/[^A-Za-z0-9\_]/', '', str_replace(' ', '_', $group_name)), 0, 10)) . '-registration_fee-' . $role_id;
  }

  /**
   * Returns the group registration product title.
   *
   * @param object $group_name The name of the group.
   * @param string $role_name The role name.
   * @return string The title of the registration product.
   */
  public static function getRegistrationProductTitle($group_name, $product_name, $role_name) {
    return t('!role !product for !group', array(
      '!role' => $role_name,
      '!product' => $product_name,
      '!group' => $group_name,
    ));
  }

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
   * @param string $for_user_uuid
   *   Optional user UUID string representing the user that the product is
   *   acually being purchased for.
   * @param boolean $installment_plan
   *   Whether or not an installment plan should be used to purchase the
   *   product.
   *
   * @return bool
   *   TRUE if succesfully added.
   */
  function usersCartAdd($user_uuid, $product_uuid, $for_user_uuid = NULL, $installment_plan = FALSE) {
    return $this->post('users/' . $user_uuid . '/add_to_cart', array(
      'product_uuid' => $product_uuid,
      'for_user_uuid' => $for_user_uuid,
      'installment_plan' => $installment_plan,
    ));
  }

  function groupStoreIndex() {
    return $this->get('group_stores');
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
   * @todo - This will require different privileges? Or should we just expect
   * the current user to have that?
   *
   * @param string $uuid
   *   Group UUID string.
   */
  function groupStoreActivate($uuid) {
    return $this->post('group_stores', array('uuid' => $uuid));
  }

  /**
   * Synchronize group store users with users on www
   *
   * @param string $uuid
   * @param boolean $admins_only
   */
  function groupStoreSyncUsers($uuid, $admins_only = TRUE) {
    return $this->post('group_stores/' . $uuid . '/sync_users', array('admins_only' => $admins_only));
  }

  /**
   * @musthave
   * @todo - List group products, optionally by type.
   *
   * @param string $group_uuid
   * @param string $type
   * @return Array
   *   Array of product objects.
   */
  function groupStoreProductsIndex($group_uuid, $type = NULL) {
    $params = ($type) ? array('type' => $type) : array();
    return $this->index('group_stores/' . $group_uuid . '/products', $params);
  }

  /**
   * Retrieve an order
   *
   * @param string $uuid
   *   The uuid of the order to get
   */
  function orderGet($order_uuid) {
    return $this->get('orders/' . $uuid);
  }

  /**
   * Create an order
   *
   * @param string $user_uuid
   *   UUID of the owner of the order
   * @param string $product_uuid
   *   UUID of the product to place in the order
   * @param string $order_status
   *   Status of new order (invoice, shopping cart, etc)
   * @param string $for_user_uuid
   *   UUID of the user the product is being purchased for
   * @param string $due_date
   *   Due date. Applicable only if order_status is invoice.
   *
   * @return stdClass
   *   Created object from api
   */
  function orderCreate($user_uuid, $product_uuid, $order_status = NULL, $for_user_uuid = NULL, $due_date = NULL, $billing_address = array(), $shipping_address = array()) {
    $params = array(
      'user_uuid' => $user_uuid,
      'product_uuid' => $product_uuid,
      'order_status' => $order_status,
      'for_user_uuid' => $for_user_uuid,
      'due_date' => $due_date,
      'billing_address' => array_filter($billing_address),
      'shipping_address' => array_filter($shipping_address),
    );
    return $this->post('orders', array_filter($params));
  }

  /**
  * Add Payment to an order.
  *
  * @param string $order_uuid
  *   UUID of the order the payment is getting applied to
  * @param string $payment_type
  *   What type of payment is it (in_person, ad_hoc, etc)
  * @param string $payment_amount
  *   The amount of the payment
  * @param array $payment_details
  *   Additional details for payment_type

  * @return bool
  *   TRUE or string with payment instructions (for in_person payments)
  */
  function orderAddPayment($order_uuid, $payment_type, $payment_amount, $payment_details = array()) {
    $params = array(
      'payment_type' => $payment_type,
      'payment_amount' => $payment_amount,
      'payment_details' => $payment_details,
    );
    return $this->post('orders/' . $order_uuid . '/add_payment', array_filter($params));
  }

  /**
   * @nicetohave
   * @param string $uuid
   */
  function productGet($uuid) {
    return $this->get('products/' . $uuid);
  }


  /**
   * Create a product in a group
   *
   * @param string $type
   *   The type of product to be created.
   * @param string $group_uuid
   *   UUID of the group that the product belongs to.
   * @param integer $role_id
   *   Numeric id of the role that the product is being created for.
   * @param string $role_name
   *   Name of the role that the product is being created for.
   * @param boolean $installments_enabled
   *   Whether or not installments should be enabled for this product.
   * @param float $initial_payment
   *   Price of the initial payment if purchased with installments.
   * @param array $installments
   *   Array of installment payments. Each payment should have a "due_date" and
   *   "amount".
   * @param float $total
   *   Full price of the product if purchased without installments.
   * @param string $sku
   *   SKU for the new product, only required for "product" products.
   * @param string $title
   *   Title for the new product, only required for "product" products.
   */
  function productCreate($type, $group_uuid, $role_id, $role_name, $installments_enabled, $initial_payment, $installments, $total, $sku, $title) {
    $params = array(
      'type' => $type,
      'group_uuid' => $group_uuid,
      'role_id' => $role_id,
      'role_name' => $role_name,
      'installments_enabled' => $installments_enabled,
      'initial_payment' => $initial_payment,
      'installments' => $installments,
      'total' => $total,
      'sku' => $sku,
      'title' => $title,
    );
    return $this->post('products', array_filter($params));
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

  /**
   * Login via user endpoint. (Overriding)
   *
   * @param string $user
   *   username
   * @param string $pass
   *   password
   */
  public function userLogin($user, $pass) {
    // Changing login path to 'user/login' (was 'users/login').
    // 'user/' path is from core services. 'users/' path is custom resource.
    $ret = $this->post('user/login', array('username' => $user, 'password' => $pass));
    $this->session = array('session_name' => $ret->session_name, 'sessid' => $ret->sessid);
    return $ret;
  }

  /**
   * Generate the embed HTML for a group store donation form.
   *
   * @param string $uuid
   *   UUID of the group with a group store.
   * @return string
   *   HTML embed snip.  Requires JS on the client.
   */
  public function embedDonateHtml($uuid) {
    return "<script src='{$this->base_url}/groups/{$uuid}/donation-embed/js'></script>";
  }

  /**
   * Set the group payment methods.
   *
   * @param string $group_uuid
   * @param string $method
   * @param array $method_info
   * @return Array
   *   Array of payment methods.
   */
  function groupPaymentMethodSet($group_uuid, $method, $method_info = array()) {
    return $this->post('group_stores/' . $group_uuid . '/payment_method', array('method' => $method, 'method_info' => $method_info));
  }

  /**
   * Get the group payment methods.
   *
   * @param string $group_uuid
   * @param string $method
   * @return Array
   *   Array of payment methods.
   */
  function groupPaymentMethodGet($group_uuid, $method = NULL) {
    if (is_null($method)) {
      return $this->get('group_stores/' . $group_uuid . '/payment_methods');
    }
    else {
      return $this->get('group_stores/' . $group_uuid . '/payment_methods', array('method' => $method));
    }
  }

  /**
   * Get the group payee.
   *
   * @param string $group_uuid
   * @return string The groups payee uuid.
   */
  function groupPayeeGet($group_uuid) {
    return $this->get('group_stores/' . $group_uuid . '/payee');
  }

  /**
   * Set the group payee.
   *
   * @param string $group_uuid
   * @param string $payee_uuid
   *   If not set, then use own payment configuration.
   * @return bool
   *   TRUE if succesfully added.
   */
  function groupPayeeSet($group_uuid, $payee_uuid = NULL) {
    if (is_null($payee_uuid)) {
      return $this->post('group_stores/' . $group_uuid . '/payee');
    }
    else {
      return $this->post('group_stores/' . $group_uuid . '/payee', array('payee_uuid' => $payee_uuid));
    }
  }

}
