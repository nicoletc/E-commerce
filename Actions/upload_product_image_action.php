<?php
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/product_controller.php';

if (!is_logged_in() || !is_admin()) json_response(['status'=>'error','message'=>'Not authorized'], 401);

$id     = (int)($_POST['product_id'] ?? 0);
$cat    = (int)($_POST['product_cat'] ?? 0);
$brand  = (int)($_POST['product_brand'] ?? 0);
$title  = trim($_POST['product_title'] ?? '');
$price  = trim($_POST['product_price'] ?? '');
$desc   = trim($_POST['product_desc'] ?? '');
$kw     = trim($_POST['product_keywords'] ?? '');

if ($id<=0 || $cat<=0 || $brand<=0 || $title==='' || $price==='') {
  json_response(['status'=>'error','message'=>'Missing or invalid fields'], 422);
}

$imgPath = null;
if (!empty($_FILES['product_image']['name'])) {
  require_once __DIR__ . '/upload_helpers.php';
  $save = save_uploaded_image_strict('product_image', (int)$_SESSION[SESS_USER_ID], $id);
  if (!$save['ok']) json_response(['status'=>'error','message'=>'Image upload failed: '.$save['error']], 400);
  $imgPath = $save['relative'];
}

$ok = update_product_ctr([
  'product_id' => $id,
  'product_cat' => $cat,
  'product_brand' => $brand,
  'product_title' => $title,
  'product_price' => $price,
  'product_desc' => $desc,
  'product_keywords' => $kw,
  'product_image' => $imgPath
]);

if (!$ok) json_response(['status'=>'error','message'=>'Update failed'], 500);
json_response(['status'=>'success','message'=>'Product updated','product_id'=>$id,'image'=>$imgPath]);
