<?php
ini_set('display_errors','1'); error_reporting(E_ALL);
if (session_status()===PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

require_once __DIR__.'/../Controllers/cart_controller.php';
require_once __DIR__.'/../Controllers/order_controller.php';

// 1) Get cart items
$items = get_user_cart_ctr();
if (!$items) { echo json_encode(['status'=>'error','message'=>'Cart is empty']); exit; }

// 2) Require a customer for orders table (schema requires customer_id NOT NULL).
// If user is a guest, we create a pseudo customer row OR return an error.
// For demo: enforce login for checkout, but allow add-to-cart for guests.
$customer_id = $_SESSION['customer_id'] ?? null;
if (!$customer_id) {
  echo json_encode(['status'=>'error','message'=>'Please log in to complete checkout. Your cart is saved.']);
  exit;
}

// 3) Compute totals
$total = 0.0;
foreach ($items as $it) { $total += ((float)$it['product_price']) * ((int)$it['qty']); }

// 4) Create order + rows
$invoice = random_int(100000, 999999);
$order_id = create_order_ctr((int)$customer_id, $invoice, 'paid');
if (!$order_id) { echo json_encode(['status'=>'error','message'=>'Could not create order']); exit; }

foreach ($items as $it) {
  if (!add_order_detail_ctr($order_id, (int)$it['p_id'], (int)$it['qty'])) {
    echo json_encode(['status'=>'error','message'=>'Failed adding order item']); exit;
  }
}

// 5) Record payment (simulated "paid")
if (!record_payment_ctr($total, (int)$customer_id, (int)$order_id, 'GHS')) {
  echo json_encode(['status'=>'error','message'=>'Payment record failed']); exit;
}

// 6) Empty cart
empty_cart_ctr();

// 7) Done
echo json_encode([
  'status'   => 'ok',
  'message'  => 'Order completed',
  'order_id' => $order_id,
  'invoice'  => $invoice,
  'total'    => $total
]);
