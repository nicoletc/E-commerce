<?php
ini_set('display_errors',1); ini_set('display_startup_errors',1); error_reporting(E_ALL);
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/product_controller.php';

if (!is_logged_in()) json_response(['status'=>'error','message'=>'Not authorized'], 401);

try {
  $data = get_products_ctr();
  json_response(['status'=>'success','data'=>$data]);
} catch (Throwable $e) {
  json_response(['status'=>'error','message'=>'Server error: '.$e->getMessage()], 500);
}
