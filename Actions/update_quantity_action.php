<?php
ini_set('display_errors','1'); error_reporting(E_ALL);
if (session_status()===PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

require_once __DIR__.'/../Controllers/cart_controller.php';

$p = (int)($_POST['product_id'] ?? 0);
$q = max(1, (int)($_POST['qty'] ?? 1));
$ok = ($p && $q) ? update_cart_item_ctr($p,$q) : false;
echo json_encode($ok ? ['status'=>'ok'] : ['status'=>'error','message'=>'Update failed']);
