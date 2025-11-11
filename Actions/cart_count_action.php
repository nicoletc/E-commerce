<?php
declare(strict_types=1);

ini_set('display_errors','1');
ini_set('display_startup_errors','1');
error_reporting(E_ALL);
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../Controllers/cart_controller.php';

try {
  // If not logged in, return 0 for now (guest carts later)
  if (empty($_SESSION['customer_id'])) {
    echo json_encode(['count' => 0]);
    exit;
  }

  $cid = (int)$_SESSION['customer_id'];

  // Prefer a lightweight count call if available; otherwise sum items
  if (function_exists('count_cart_items_ctr')) {
    $count = (int)count_cart_items_ctr($cid);
  } else {
    $items = get_user_cart_ctr($cid);          // must return an array of rows with 'qty'
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
