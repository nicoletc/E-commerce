<?php
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/product_controller.php';

if (!is_logged_in()) json_response(['status'=>'error','message'=>'Not authorized'], 401);

$id = (int)($_GET['product_id'] ?? 0);
if ($id <= 0) json_response(['status'=>'error','message'=>'Invalid product id'], 400);

$item = get_product_ctr($id);
if (!$item) json_response(['status'=>'error','message'=>'Not found'], 404);

json_response(['status'=>'success','data'=>$item]);
