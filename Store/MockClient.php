<?php
namespace AllPlayers\Store;

class MockClient extends Client{

  /**
   * @nicetohave
   * Line items in the cart services users cart.
   *
   * @return Array
   *   Array of cart line item objects.
   */
  function usersCartIndex() {
    //return $this->index('users/mycart');
    return array(
      array('item' => 'Foo', 'quantity' => '2', 'unit_price' => '100.00', 'line_price' => '200.00'),
      array('item' => 'Bar', 'quantity' => '1', 'unit_price' => '50.00', 'line_price' => '50.00'),
    );
  }

  /**
   * @musthave
   * Add items to the services users cart.
   *
   * @return bool
   *   TRUE if succesfully added.
   */
  function usersCartAdd($product_uuid) {
    return TRUE;
  }

  /**
   * @musthave
   * @todo - Get information about a group store if it exists.
   *
   * @param string $uuid
   *   Group UUID string.
   */
  function groupStoreGet($uuid) {
    //$this->get('group_stores/' . $uuid);
    $group = new \stdClass();
    $group->uuid = $uuid;
    $group->title = 'The test group';
    $group->store_status = '1';
    return $group;
  }

  /**
   * @nicetohave
   * @todo - Initialize and enable a group store now.
   * @todo - This will require different privileges? Or should we just expect the current user to have that?
   *
   * @param string $uuid
   *   Group UUID string.
   */
  function groupStoreEnable($uuid) {
    //return $this->put('group_stores/' . $uuid . '/enable');
    return array('success' => '1');
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
    //$params = ($type) ? array('type' => $type) : array();
    //return $this->index('group_stores/' . $group_uuid . '/products', $params);
    return array(
      $this->productGet(0),
      $this->productGet(1),
    );
  }

  /**
   * @nicetohave
   * @param unknown_type $uuid
   */
  function productGet($uuid) {
    //return $this->get('products/' . $uuid);
    $product = new \stdClass();
    if ($uuid == 1) {
      $product->title = 'Registration for Player';
      $product->uuid = '11cf8960-ef54-11e0-be50-0800200c9a66';
      $product->link = $this->base_url . '/products/321';
      $product->type = 'registration';
      $product->roleName = 'Player';
      $product->rid = '321';
      $product->price = '100.00';
      $product->installmentsAllowed = '1';
      $product->installmetsInitialPrice = '50.00';
      $product->installments = array(
        'date' => '5/23/2012', 'price' => '40.00',
        'date' => '6/20/2012', 'price' => '30.00',
      );
    }
    else {
      $product->title = 'Registration for Coach';
      $product->uuid = '275e7cf0-ef54-11e0-be50-0800200c9a66';
      $product->link = $this->base_url . '/products/123';
      $product->type = 'registration';
      $product->roleName = 'Coach';
      $product->rid = '123';
      $product->price = '20.00';
      $product->installmentsAllowed = '0';
    }

    return $product;
  }
}
