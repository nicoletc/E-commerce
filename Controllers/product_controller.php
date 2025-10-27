<?php
// Controllers/product_controller.php
require_once __DIR__ . '/../Classes/product_class.php';

function add_product_ctr(array $args) {
  $db = new product_class();
  $payload = [
    'product_cat'      => (int)($args['product_cat'] ?? 0),
    'product_brand'    => (int)($args['product_brand'] ?? 0),
    'product_title'    => trim($args['product_title'] ?? ''),
    'product_price'    => (string)($args['product_price'] ?? ''), // keep as string/decimal
    'product_desc'     => trim($args['product_desc'] ?? ''),
    'product_keywords' => trim($args['product_keywords'] ?? ''),
    'product_image'    => $args['product_image'] ?? null,         // relative path or null
  ];
  return $db->add_product($payload);
}

function update_product_ctr(array $args) {
  $db = new product_class();
  $payload = [
    'product_id'       => (int)($args['product_id'] ?? 0),
    'product_cat'      => (int)($args['product_cat'] ?? 0),
    'product_brand'    => (int)($args['product_brand'] ?? 0),
    'product_title'    => trim($args['product_title'] ?? ''),
    'product_price'    => (string)($args['product_price'] ?? ''),
    'product_desc'     => trim($args['product_desc'] ?? ''),
    'product_keywords' => trim($args['product_keywords'] ?? ''),
    'product_image'    => $args['product_image'] ?? null,
  ];
  return $db->update_product($payload);
}

function get_product_ctr(int $id) {
  $db = new product_class();
  return $db->get_product($id);
}

function get_products_ctr() {
  $db = new product_class();
  return $db->get_products();
}
