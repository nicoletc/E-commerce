<?php
// Actions/product_actions.php
declare(strict_types=1);

ini_set('display_errors','1');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../Controllers/product_controller.php';

// Accept only GET for these reads
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
  http_response_code(405);
  echo json_encode(['status'=>'error','message'=>'Method not allowed']); exit;
}

try {
  // If id given → single view
  if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $row = view_single_product_ctr((int)$_GET['id']);
    if (!$row) { http_response_code(404); echo json_encode(['status'=>'error','message'=>'Not found']); exit; }
    echo json_encode(['status'=>'success','data'=>$row]); exit;
  }


$page      = isset($_GET['page'])      ? max(1, (int)$_GET['page']) : 1;
$limit     = isset($_GET['limit'])     ? max(1, min(50,(int)$_GET['limit'])) : 10;
$q         = trim($_GET['q'] ?? '');
$cat       = isset($_GET['cat'])   && $_GET['cat']   !== '' ? (int)$_GET['cat']   : null;
$brand     = isset($_GET['brand']) && $_GET['brand'] !== '' ? (int)$_GET['brand'] : null;
// NEW:
$min_price = isset($_GET['min_price']) ? $_GET['min_price'] : '';
$max_price = isset($_GET['max_price']) ? $_GET['max_price'] : '';

$data = view_all_products_ctr([
  'page'       => $page,
  'limit'      => $limit,
  'q'          => $q ?: null,
  'cat'        => $cat,
  'brand'      => $brand,
  'min_price'  => $min_price,
  'max_price'  => $max_price,
]);

echo json_encode(['status'=>'success'] + $data);


} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
