<?php
ini_set('display_errors','1'); error_reporting(E_ALL);
if (session_status()===PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

require_once __DIR__.'/../Controllers/cart_controller.php';

 $p = (int)($_POST['product_id'] ?? 0);
 $ok = ($p) ? remove_from_cart_ctr(null, $p) : false;
 echo json_encode($ok ? ['status'=>'ok'] : ['status'=>'error','message'=>'Remove failed']);
