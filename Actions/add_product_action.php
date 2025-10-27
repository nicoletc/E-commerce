<?php
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/product_controller.php';

if (!is_logged_in() || !is_admin()) json_response(['status'=>'error','message'=>'Not authorized'], 401);

$user_id = (int)$_SESSION[SESS_USER_ID];

// Basic required fields
$cat    = (int)($_POST['product_cat']   ?? 0);
$brand  = (int)($_POST['product_brand'] ?? 0);
$title  = trim($_POST['product_title']  ?? '');
$price  = trim($_POST['product_price']  ?? '');
$desc   = trim($_POST['product_desc']   ?? '');
$kw     = trim($_POST['product_keywords'] ?? '');

if ($cat<=0 || $brand<=0 || $title==='' || $price==='') {
  json_response(['status'=>'error','message'=>'Please fill category, brand, title and price'], 422);
}

// First create product without image to get product_id
$tmpArgs = [
  'user_id' => $user_id,
  'product_cat' => $cat,
  'product_brand' => $brand,
  'product_title' => $title,
  'product_price' => $price,
  'product_desc' => $desc,
  'product_keywords' => $kw,
  'product_image' => null
];
$product_id = add_product_ctr($tmpArgs);
if (!$product_id) json_response(['status'=>'error','message'=>'Failed to create product'], 500);

// Handle optional image
$finalPath = null;
if (!empty($_FILES['product_image']['name'])) {
  require_once __DIR__ . '/upload_helpers.php';
  $save = save_uploaded_image_strict('product_image', $user_id, $product_id);
  if (!$save['ok']) {
    json_response(['status'=>'error','message'=>'Image upload failed: '.$save['error']], 400);
  }
  $finalPath = $save['relative'];
  // update record with image path
  update_product_ctr([
    'product_id' => $product_id,
    'product_cat' => $cat,
    'product_brand' => $brand,
    'product_title' => $title,
    'product_price' => $price,
    'product_desc' => $desc,
    'product_keywords' => $kw,
    'product_image' => $finalPath
  ]);
}

json_response(['status'=>'success','message'=>'Product added','product_id'=>$product_id,'image'=>$finalPath]);
