<?php
// Controllers/product_controller.php
require_once __DIR__ . '/../Classes/product_class.php';

function add_product_ctr($args) {
  $db = new product_class();
  return $db->add_product(
    $args['user_id'],
    (int)$args['product_cat'],
    (int)$args['product_brand'],
    $args['product_title'],
    $args['product_price'],
    $args['product_desc'],
    $args['product_keywords'],
    $args['product_image'] // relative path or null
  );
}

function update_product_ctr($args) {
  $db = new product_class();
  return $db->update_product(
    (int)$args['product_id'],
    (int)$args['product_cat'],
    (int)$args['product_brand'],
    $args['product_title'],
    $args['product_price'],
    $args['product_desc'],
    $args['product_keywords'],
    $args['product_image'] ?? null
  );
}

function get_product_ctr($id) {
  $db = new product_class();
  return $db->get_product((int)$id);
}

function get_products_ctr() {
  $db = new product_class();
  return $db->get_products();
}
