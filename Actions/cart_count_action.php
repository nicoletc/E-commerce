<?php
declare(strict_types=1);

ini_set('display_errors','1');
ini_set('display_startup_errors','1');
error_reporting(E_ALL);
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../Controllers/cart_controller.php';

try {
  // Count for logged-in or guest holder (controller will resolve)
  $cid = (int)($_SESSION['customer_id'] ?? 0);
  if (function_exists('count_cart_items_ctr')) {
    $count = (int)count_cart_items_ctr($cid ?: null);
  } else {
    $items = get_user_cart_ctr($cid ?: null);          // must return an array of rows with 'qty'
    $count = 0;
    foreach ((array)$items as $row) {
      $count += (int)($row['qty'] ?? 0);
    }
  }

  echo json_encode(['count' => $count]);
} catch (Throwable $e) {
  // Do not 500 the page; fail gracefully so the UI can continue
  echo json_encode(['count' => 0, 'error' => $e->getMessage()]);
}
