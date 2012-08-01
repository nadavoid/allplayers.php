<?php
namespace AllPlayers\Store;

use Log;
use DateTime;
use DateTimeZone;

use AllPlayers\Component\HttpClient;
use InvalidArgumentException;

class Client extends HttpClient {
  /**
   * Proper format for dates.
   *
   * @var string
   */
  const DATE_FORMAT = 'Y-m-d';

  /**
   * Proper format for dates with time.
   *
   * @var string
   */
  const DATETIME_FORMAT = 'Y-m-d\TH:i:00';

  // @todo - This isn't configurable upstream.
  const ENDPOINT = '/api/v1/rest';

  /**
   * Default AllPlayers.com Store URL.
   *
   * @var string
   */
  public $base_url = NULL;

  /**
   * Headers variable for setting on an http request
   */
  private $headers = array();

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
   * Adds headers to the http request.
   * @param string $key
   *   e.g. "User-Agent"
   * @param string $val
   *   e.g. "Chrome"
   */
  public function addHeader ($key, $val) {
    $this->headers[$key] = $val;
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
   * @param integer $role_id
   *   Role id to associate this purchase with for registration.
   *
   * @return bool
   *   TRUE if succesfully added.
   */
  function usersCartAdd($user_uuid, $product_uuid, $for_user_uuid = NULL, $installment_plan = FALSE, $role_id = NULL) {
    return $this->post('users/' . $user_uuid . '/add_to_cart', array(
      'product_uuid' => $product_uuid,
      'for_user_uuid' => $for_user_uuid,
      'installment_plan' => $installment_plan,
      'role_id' => $role_id,
    ), $this->headers);
  }

  /**
   * Return the group stores.
   *
   * @param string $user_uuid Filter the results based on the membership of this user.
   * @param boolean $is_admin Filter the results futher based on if user_uuid is an admin of those groups.
   * @param boolean $accepts_payment Filter the results futher based on if the user is an admin of a group that accepts their own payments.
   * @return type
   */
  function groupStoreIndex($user_uuid = '', $is_admin = FALSE, $accepts_payment = FALSE) {
    return $this->get('group_stores', array(
      'user_uuid' => $user_uuid,
      'is_admin' => $is_admin ? 1 : 0,
      'accepts_payment' => $accepts_payment ? 1 : 0,
    ));
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
    return $this->get('group_stores/' . $uuid, array(), $this->headers);
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
    return $this->post('group_stores', array('uuid' => $uuid), $this->headers);
  }

  /**
   * Synchronize group store users with users on www
   *
   * @param string $uuid
   * @param boolean $admins_only
   */
  function groupStoreSyncUsers($uuid, $admins_only = TRUE, $og_role = NULL) {
    return $this->post('group_stores/' . $uuid . '/sync_users', array('admins_only' => $admins_only, 'og_role' => $og_role), $this->headers);
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
   * Line Items Index
   */
  function lineItemsIndex($originating_order_uuid = NULL, $product_uuid = NULL, $originating_product_uuid = NULL, $line_item_type = NULL, $user_uuid = NULL, $fields = NULL, $pagesize = 0, $page = 0) {
    $params = array(
      'originating_order_uuid' => $originating_order_uuid,
      'originating_product_uuid' => $originating_product_uuid,
      'line_item_type' => $line_item_type,
      'product_uuid' => $product_uuid,
      'user_uuid' => $user_uuid,
    );

    return $this->index('line_items', array_filter($params), $fields, $page, $pagesize);
  }
  /**
   * Retrieve an order
   *
   * @param string $uuid
   *   The uuid of the order to get
   */
  function orderGet($order_uuid) {
    return $this->get('orders/' . $uuid, array(), $this->headers);
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
   * @param DateTime $due_date
   *   Due date. Applicable only if order_status is invoice.
   * @param array $billing_address
   *   Billing address of the user.
   * @param array $shipping_address
   *   Shipping address of the user.
   * @param DateTime $created
   *   Created on date and time of the order. If omitted, the current date and
   *   time will be used.
   * @param int $initial_payment_only
   *   Whether to generate automatically the installments
   *
   * @return stdClass
   *   Created object from api
   */
  function orderCreate($user_uuid, $product_uuid, $order_status = NULL, $for_user_uuid = NULL, DateTime $due_date = NULL, $billing_address = array(), $shipping_address = array(), $installment_plan = 0, DateTime $created = NULL, $initial_payment_only = 0) {
    $params = array(
      'user_uuid' => $user_uuid,
      'product_uuid' => $product_uuid,
      'order_status' => $order_status,
      'for_user_uuid' => $for_user_uuid,
      'due_date' => (!empty($due_date)
        ? $due_date->format(self::DATE_FORMAT)
        : NULL),
      'billing_address' => empty($billing_address) ? NULL : array_filter($billing_address),
      'shipping_address' => empty($shipping_address) ? NULL : array_filter($shipping_address),
      'installment_plan' => $installment_plan,
      'created' => (!empty($created)
        ? $created->setTimezone(new DateTimeZone('UTC'))->format(self::DATETIME_FORMAT)
        : NULL),
      'initial_payment_only' => $initial_payment_only,
    );

    return $this->post('orders', array_filter($params), $this->headers);
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
   * @param DateTime $created
   *   Created on date and time of the payment. If omitted, the current date and
   *   time will be used.
   *
   * @return bool
   *   TRUE or string with payment instructions (for in_person payments)
   */
  function orderAddPayment($order_uuid, $payment_type, $payment_amount, $payment_details = array(), DateTime $created) {
    $params = array(
      'payment_type' => $payment_type,
      'payment_amount' => $payment_amount,
      'payment_details' => $payment_details,
      'created' => (!empty($created)
        ? $created->setTimezone(new DateTimeZone('UTC'))->format(self::DATETIME_FORMAT)
        : NULL),
    );

    return $this->post('orders/' . $order_uuid . '/add_payment', array_filter($params), $this->headers);
  }

  /**
   *
   * @param string $order_uuid
   *   UUID of the order.
   * @param int $series_id
   *   Which installment to create an invoice for. Use numbers 0 through number of installments - 1.
   * @param DateTime $created
   *   When the invoice was created
   * @return stdClass
   *   Object from api
   */
  function orderAddInstallmentInvoice($order_uuid, $series_id, DateTime $created) {
    $params = array(
      'series_id' => $series_id,
      'created' => (!empty($created)
          ? $created->setTimezone(new DateTimeZone('UTC'))->format(self::DATETIME_FORMAT)
          : NULL),
    );
    return $this->post('orders/' . $order_uuid . '/add_installment_invoice', $params, $this->headers);
  }
  /**
   * @nicetohave
   * @param string $uuid
   */
  function productGet($uuid) {
    return $this->get('products/' . $uuid, array(), $this->headers);
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
   *   "amount". The due date should be an object of type DateTime.
   * @param float $total
   *   Full price of the product if purchased without installments.
   * @param string $sku
   *   SKU for the new product, only required for "product" products.
   * @param string $title
   *   Title for the new product, only required for "product" products.
   *
   * @return stdClass
   *   The new product as returend by the API.
   *
   * @todo Accept a product object rather than all of these arguments.
   */
  function productCreate($type, $group_uuid, $role_id = NULL, $role_name = NULL, $installments_enabled = 0, $initial_payment = 0, $installments = array(), $total = 0, $sku = NULL, $title = NULL) {
    // Iterate over all installments, setting their due dates to the appropriate
    // format.
    if (!empty($installments)) {
      foreach ($installments as $delta => $installment) {
        if (!($installment['due_date'] instanceof DateTime)) {
          throw new InvalidArgumentException('Invalid argument: Installment due date must be an object of type DateTime.');
        }

        $installments[$delta]['due_date'] = $installment['due_date']->format(self::DATE_FORMAT);
      }
    }

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

    return $this->post('products', array_filter($params), $this->headers);
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
    $ret = $this->post('user/login', array('username' => $user, 'password' => $pass), $this->headers);
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
    return $this->post('group_stores/' . $group_uuid . '/payment_method', array('method' => $method, 'method_info' => $method_info), $this->headers);
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
      return $this->get('group_stores/' . $group_uuid . '/payment_methods', array(), $this->headers);
    }
    else {
      return $this->get('group_stores/' . $group_uuid . '/payment_methods', array('method' => $method), $this->headers);
    }
  }

  /**
   * Get the group payee.
   *
   * @param string $group_uuid
   * @return string The groups payee uuid.
   */
  function groupPayeeGet($group_uuid) {
    return $this->get('group_stores/' . $group_uuid . '/payee', array(), $this->headers);
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
      return $this->post('group_stores/' . $group_uuid . '/payee', array(), $this->headers);
    }
    else {
      return $this->post('group_stores/' . $group_uuid . '/payee', array('payee_uuid' => $payee_uuid), $this->headers);
    }
  }

}
