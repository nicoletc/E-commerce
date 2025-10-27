<?php
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/brand_controller.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { json_response(['status'=>'error','message'=>'Invalid method'],405); }

$payload = json_decode(file_get_contents('php://input'), true) ?? [];
$name  = $payload['brand_name'] ?? '';

$res = add_brand_ctr($name);
json_response($res);
