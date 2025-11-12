<?php
require_once __DIR__.'/../Classes/cart_class.php';
require_once __DIR__ . '/../settings/core.php';

// Helper to resolve customer id from parameter or session
function resolve_customer_id(?int $cid = null): int {
  if ($cid && $cid > 0) return $cid;
  return (int)($_SESSION['customer_id'] ?? 0);
}

/**
 * Resolve current cart holder. Returns array with keys:
 * - type: 'customer' or 'guest'
 * - id: customer id (int) or token (string)
 */
function resolve_cart_holder(?int $cid = null){
  $c = resolve_customer_id($cid);
  if ($c > 0) return ['type' => 'customer', 'id' => $c];

  // ensure a guest token exists in session
  if (empty($_SESSION['guest_holder'])) {
    // use a short random token
    $_SESSION['guest_holder'] = bin2hex(random_bytes(8));
  }
  return ['type' => 'guest', 'id' => $_SESSION['guest_holder']];
}

/**
 * Add to cart. If $customer_id is omitted, the function will use the session customer id.
 */
function add_to_cart_ctr(?int $customer_id, int $product_id, int $qty = 1, string $ip = '0.0.0.0') {
  $holder = resolve_cart_holder($customer_id);
  $m = new cart_class();
  if ($holder['type'] === 'customer') {
    return $m->add_item((int)$holder['id'], $product_id, $qty, $ip);
  }
  // guest
  return $m->add_item(0, $product_id, $qty, $holder['id']);
}

/**
 * Update quantity for a cart item. $customer_id optional.
 */
function update_cart_item_ctr(?int $customer_id, int $p_id, int $qty){
  $holder = resolve_cart_holder($customer_id);
  $c = new cart_class();
  if ($holder['type'] === 'customer') {
    return $c->update_qty((int)$holder['id'], $p_id, $qty);
  }
  return $c->update_qty_by_token($holder['id'], $p_id, $qty);
}

/**
 * Remove an item from cart. $customer_id optional.
 */
function remove_from_cart_ctr(?int $customer_id, int $p_id){
  $holder = resolve_cart_holder($customer_id);
  $c = new cart_class();
  if ($holder['type'] === 'customer') {
    return $c->remove_item((int)$holder['id'], $p_id);
  }
  return $c->remove_item_by_token($holder['id'], $p_id);
}

/**
 * Get cart items for a customer. If omitted, uses session customer id.
 */
function get_user_cart_ctr(?int $c_id = null) {
  $holder = resolve_cart_holder($c_id);
  $m = new cart_class();
  if ($holder['type'] === 'customer') {
    return $m->get_cart_items((int)$holder['id']);
  }
  return $m->get_cart_items_by_token($holder['id']);
}

function count_cart_items_ctr(?int $c_id = null): int {
  $holder = resolve_cart_holder($c_id);
  $m = new cart_class();
  if ($holder['type'] === 'customer') {
    return (int)$m->count_items((int)$holder['id']);
  }
  return (int)$m->count_items_by_token($holder['id']);
}

function empty_cart_ctr(?int $c_id = null){
  $holder = resolve_cart_holder($c_id);
  $c = new cart_class();
  if ($holder['type'] === 'customer') {
    return $c->empty_cart((int)$holder['id']);
  }
  return $c->empty_cart_by_token($holder['id']);
}

function count_cart_ctr(?int $c_id = null){
  return count_cart_items_ctr($c_id);
}

function current_cart_holder_ctr(){
  // Return current holder info for debugging/clients
  $holder = resolve_cart_holder(null);
  return $holder;
}

function migrate_guest_cart_ctr(?int $c_id = null){
  $cid = resolve_customer_id($c_id);
  if ($cid <= 0) return false;
  // if there is a guest token, migrate
  if (!empty($_SESSION['guest_holder'])){
    $c = new cart_class();
    return $c->migrate_guest_to_user($cid, $_SESSION['guest_holder']);
  }
  return false;
}
