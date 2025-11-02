<?php
// Controllers/product_controller.php
require_once __DIR__ . '/../Classes/product_class.php';


function add_product_ctr(array $args) {
  $db = new product_class();
  return $db->add_product([
    'product_cat'      => (int)($args['product_cat'] ?? 0),
    'product_brand'    => (int)($args['product_brand'] ?? 0),
    'product_title'    => (string)($args['product_title'] ?? ''),
    'product_price'    => (string)($args['product_price'] ?? ''),
    'product_desc'     => (string)($args['product_desc'] ?? ''),
    'product_keywords' => (string)($args['product_keywords'] ?? ''),
    'product_image'    => $args['product_image'] ?? null,
  ]);
}




function update_product_ctr(array $args) {
  $db = new product_class();
  return $db->update_product([
    'product_id'       => (int)($args['product_id'] ?? 0),
    'product_cat'      => (int)($args['product_cat'] ?? 0),
    'product_brand'    => (int)($args['product_brand'] ?? 0),
    'product_title'    => (string)($args['product_title'] ?? ''),
    'product_price'    => (string)($args['product_price'] ?? ''),
    'product_desc'     => (string)($args['product_desc'] ?? ''),
    'product_keywords' => (string)($args['product_keywords'] ?? ''),
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


function view_all_products_ctr(array $opts = []) {
  $db = new product_class();
  return $db->view_all_products($opts);
}


function view_single_product_ctr(int $id) {
  $db = new product_class();
  return $db->view_single_product($id);         // returns one row or null
}

function search_products_ctr(array $opts = []) {
  $db = new product_class();
  return $db->search_products($opts);           // returns rows[]
}

/** Convenience wrappers using the same unified query path */
function filter_products_by_category_ctr(int $cat, array $opts = []) {
  $opts['cat'] = $cat;
  $db = new product_class();
  return $db->view_all_products($opts);         // returns rows[]
}

function filter_products_by_brand_ctr(int $brand, array $opts = []) {
  $opts['brand'] = $brand;
  $db = new product_class();
  return $db->view_all_products($opts);         // returns rows[]
}

function count_all_products_ctr(array $opts = []) {
  $db = new product_class();
  return $db->count_all_products($opts);        // returns int
}

function count_search_products_ctr(array $opts = []) {
  $db = new product_class();
  return $db->count_search_products($opts);     // returns int
}

function get_categories_ctr() {
  $db = new product_class();
  return $db->get_categories();
}

function get_brands_ctr() {
  $db = new product_class();
  return $db->get_brands();
}
