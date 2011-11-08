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
    $return = array();

    $line_item = new \stdClass();
    $line_item->quantity = 2;
    $line_item->title = 'Foo';
    $line_item->unit_price = '$100.00';
    $line_item->line_price = '$200.00';
    $line_item->group_name = 'Test Group';
    $return[] = $line_item;

    $line_item = new \stdClass();
    $line_item->quantity = 1;
    $line_item->title = 'Bar';
    $line_item->unit_price = '$50.00';
    $line_item->line_price = '$50.00';
    $line_item->group_name = 'Another Group';
    $return[] = $line_item;

    return $return;
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

  function groupStoreIndex() {
    $return = array();

    $group = new \stdClass();
    $group->uuid = '1234';
    $group->store_status = '0';
    $group->title = 'Test Title';
    $group->uri = $this->base_url . '/something/' . $group->uuid;
    $return[] = $group;

    $group = new \stdClass();
    $group->uuid = '2222';
    $group->store_status = '0';
    $group->title = 'Inactive Store';
    $group->uri = $this->base_url . '/something/' . $group->uuid;
    $return[] = $group;

    $group = new \stdClass();
    $group->uuid = '3334';
    $group->store_status = '1';
    $group->title = 'Another Test Store';
    $group->uri = $this->base_url . '/something/' . $group->uuid;
    $return[] = $group;

    return $return;
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
    $group->store_status = '1';
    $group->title = 'The test group';
    $group->uri = $this->base_url . '/something/' . $uuid;
    $group->uuid = $uuid;
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
      $product->installmentsAllowed = '0';
      $product->link = $this->base_url . '/products/123';
      $product->price_raw = '2000';
      $product->rid = '123';
      $product->roleName = 'Coach';
      $product->title = 'Registration for Coach';
      $product->type = 'registration';
      $product->uri = $this->base_url . '/something/' . $uuid;
      $product->uuid = '275e7cf0-ef54-11e0-be50-0800200c9a66';
    }

    return $product;
  }

}
