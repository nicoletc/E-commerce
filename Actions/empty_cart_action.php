<?php
ini_set('display_errors','1'); error_reporting(E_ALL);
if (session_status()===PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

require_once __DIR__.'/../Controllers/cart_controller.php';
$ok = empty_cart_ctr();
echo json_encode($ok ? ['status'=>'ok'] : ['status'=>'error','message'=>'Could not empty cart']);
