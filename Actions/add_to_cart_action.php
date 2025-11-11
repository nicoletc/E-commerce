<?php
// Actions/add_to_cart_action.php
ini_set('display_errors','1'); ini_set('display_startup_errors','1'); error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../Controllers/cart_controller.php';
require_once __DIR__ . '/../Controllers/product_controller.php';

$c_id = (int)($_SESSION['customer_id'] ?? 0);
$p_id = (int)($_POST['product_id'] ?? 0);
$qty  = max(1, (int)($_POST['qty'] ?? 1));
$ip   = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';



if ($c_id <= 0) { echo json_encode(['status'=>'auth','message'=>'Login required']); exit; }
if ($p_id <= 0) { echo json_encode(['status'=>'error','message'=>'Invalid product']); exit; }


$ok = add_to_cart_ctr($c_id, $p_id, $qty, $ip);
echo json_encode($ok ? ['status'=>'ok','message'=>'Added to cart'] : ['status'=>'error','message'=>'Failed to add']);


