<?php
ini_set('display_errors','1'); error_reporting(E_ALL);
if (session_status()===PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

require_once '../Controllers/cart_controller.php';
require_once '../Controllers/order_controller.php';
require_once '../settings/core.php';

// Ensure we respond JSON
header('Content-Type: application/json; charset=utf-8');

// 1) Require a logged-in customer
$customer_id = (int)($_SESSION['customer_id'] ?? 0);
if ($customer_id <= 0) {
  json_response(['status' => 'error', 'message' => 'Please log in to complete checkout. Your cart is saved.'], 401);
}

// 2) Get cart items for the logged-in customer
$items = get_user_cart_ctr($customer_id);
if (!$items) {
  json_response(['status' => 'error', 'message' => 'Cart is empty']);
}

// 3) Compute totals
$total = 0.0;
foreach ($items as $it) { $total += ((float)$it['product_price']) * ((int)$it['qty']); }

// 4) Create order + rows
try {
  // invoice/reference: use a high-entropy int for demo but keep numeric for DB
  $invoice = random_int(100000, 999999);
  $order_id = create_order_ctr($customer_id, $invoice, 'paid');
  if (!$order_id) throw new RuntimeException('Could not create order');

  foreach ($items as $it) {
    $ok = add_order_detail_ctr((int)$order_id, (int)$it['p_id'], (int)$it['qty']);
    if (!$ok) throw new RuntimeException('Failed adding order item for product ' . (int)$it['p_id']);
  }

  // 5) Record payment (simulated "paid")
  if (!record_payment_ctr($total, $customer_id, (int)$order_id, 'GHS')) {
    throw new RuntimeException('Payment record failed');
  }

  // 6) Empty cart
  empty_cart_ctr($customer_id);

  // 7) Success response
  json_response([
    'status'   => 'ok',
    'message'  => 'Order completed',
    'order_id' => (int)$order_id,
    'order_ref'=> (string)$invoice,
    'total'    => $total
  ]);

} catch (Throwable $e) {
  json_response(['status' => 'error', 'message' => $e->getMessage()], 500);
}
