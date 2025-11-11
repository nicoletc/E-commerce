<?php
require_once __DIR__.'/../Classes/cart_class.php';
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../settings/db_class.php';


function add_to_cart_ctr(int $customer_id, int $product_id, int $qty = 1, string $ip = '0.0.0.0') {
  $m = new cart_class();
  return $m->add_item($customer_id, $product_id, $qty, $ip);
}

function update_cart_item_ctr(int $c_id, int $p_id, int $qty){
  $c = new cart_class();
  return $c->update_qty($c_id, $p_id, $qty);
}

function remove_from_cart_ctr(int $c_id, int $p_id){
  $c = new cart_class();
  return $c->remove_item($c_id, $p_id);
}

function get_user_cart_ctr(int $c_id) {
  $m = new cart_class();
  return $m->get_cart_items($c_id);
}

function count_cart_items_ctr(int $c_id): int {
  $m = new cart_class();
  return (int)$m->count_items($c_id);
}

function empty_cart_ctr(int $c_id){
  $c = new cart_class();
  return $c->empty_cart($c_id);
}

function count_cart_ctr(int $c_id){
  $c = new cart_class();
  return $c->count_items($c_id);
}

function current_cart_holder_ctr(){
  $c = new cart_class();
  return $c->current_holder();
}

function migrate_guest_cart_ctr(int $c_id){
  $c = new cart_class();
  return $c->migrate_guest_to_user($c_id);
}
