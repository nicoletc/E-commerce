<?php
// Controllers/product_controller.php
require_once __DIR__ . '/../Classes/product_class.php';

function add_product_ctr(array $args) {
  $db = new product_class();
  // IMPORTANT: product_class::add_product expects a single array
  return $db->add_product([
    'product_cat'      => (int)$args['product_cat'],
    'product_brand'    => (int)$args['product_brand'],
    'product_title'    => $args['product_title'],
    'product_price'    => $args['product_price'],
    'product_desc'     => $args['product_desc'] ?? '',
    'product_keywords' => $args['product_keywords'] ?? '',
    'product_image'    => $args['product_image'] ?? null, // set later by upload
  ]);
}

function update_product_ctr(array $args) {
  $db = new product_class();
  return $db->update_product([
    'product_id'       => (int)$args['product_id'],
    'product_cat'      => (int)$args['product_cat'],
    'product_brand'    => (int)$args['product_brand'],
    'product_title'    => $args['product_title'],
    'product_price'    => $args['product_price'],
    'product_desc'     => $args['product_desc'] ?? '',
    'product_keywords' => $args['product_keywords'] ?? '',
    'product_image'    => $args['product_image'] ?? null,
  ]);
}

function get_product_ctr(int $id) {
  $db = new product_class();
  return $db->get_product($id);
}

function get_products_ctr() {
  $db = new product_class();
  return $db->get_products();
}
