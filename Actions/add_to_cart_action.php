<?php
// Actions/add_to_cart_action.php
ini_set('display_errors','1'); ini_set('display_startup_errors','1'); error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../Controllers/cart_controller.php';
require_once __DIR__ . '/../Controllers/product_controller.php';

 $p_id = (int)($_POST['product_id'] ?? 0);
 $qty  = max(1, (int)($_POST['qty'] ?? 1));
 $ip   = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';


 if ($p_id <= 0) { echo json_encode(['status'=>'error','message'=>'Invalid product']); exit; }

 // pass null for customer id so controller can resolve guest token if needed
 $ok = add_to_cart_ctr(null, $p_id, $qty, $ip);
if ($ok) {
	// return updated cart count for immediate UI update
	$count = function_exists('count_cart_items_ctr') ? (int)count_cart_items_ctr(null) : 0;
	echo json_encode(['status'=>'ok','message'=>'Added to cart', 'count' => $count]);
} else {
	echo json_encode(['status'=>'error','message'=>'Failed to add']);
}


