<?php
// Actions/fetch_product_action.php
declare(strict_types=1);

ini_set('display_errors','1');
error_reporting(E_ALL);

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/product_controller.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in() || !is_admin()) {
  http_response_code(401);
  echo json_encode(['status'=>'error','message'=>'Not authorised']);
  exit;
}

try {
  $rows = get_products_ctr();
  echo json_encode(['status'=>'success','data'=>$rows]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['status'=>'error','message'=>'Server error: '.$e->getMessage()]);
}
