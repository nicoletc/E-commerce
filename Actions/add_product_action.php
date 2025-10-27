<?php
// Actions/add_product_action.php
declare(strict_types=1);

ini_set('display_errors','1');
error_reporting(E_ALL);

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/product_controller.php';

header('Content-Type: application/json; charset=utf-8');

// must be logged in + admin
if (!is_logged_in() || !is_admin()) {
  http_response_code(401);
  echo json_encode(['status'=>'error','message'=>'You must be logged in as admin.']);
  exit;
}

// Accept JSON or form-data
$ctype = $_SERVER['CONTENT_TYPE'] ?? '';
if (stripos($ctype, 'application/json') !== false) {
  $raw = file_get_contents('php://input');
  $data = json_decode($raw, true);
  if (!is_array($data)) {
    http_response_code(400);
    echo json_encode([
      'status'=>'error',
      'message'=>'Invalid JSON: '.json_last_error_msg()
    ]);
    exit;
  }
} else {
  // form-data (e.g., when you later add an image upload)
  $data = $_POST;
}

// Normalize + validate
$payload = [
  'product_cat'      => isset($data['product_cat'])   ? (int)$data['product_cat']   : 0,
  'product_brand'    => isset($data['product_brand']) ? (int)$data['product_brand'] : 0,
  'product_title'    => trim($data['product_title']    ?? ''),
  'product_price'    => trim($data['product_price']    ?? ''),
  'product_desc'     => trim($data['product_desc']     ?? ''),
  'product_keywords' => trim($data['product_keywords'] ?? ''),
  'product_image'    => $data['product_image'] ?? null, // set by upload step (can be null)
];

$errors = [];
if ($payload['product_cat'] <= 0)   $errors[] = 'Select a valid category.';
if ($payload['product_brand'] <= 0) $errors[] = 'Select a valid brand.';
if ($payload['product_title'] === '') $errors[] = 'Title is required.';
if ($payload['product_price'] === '' || !is_numeric($payload['product_price'])) $errors[] = 'Price must be numeric.';

if ($errors) {
  http_response_code(400);
  echo json_encode(['status'=>'error','message'=>implode(' ', $errors)]);
  exit;
}

try {
  $newId = add_product_ctr($payload);
  echo json_encode(['status'=>'success','product_id'=>$newId]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['status'=>'error','message'=>'Server error: '.$e->getMessage()]);
}
