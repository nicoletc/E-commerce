<?php
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/brand_controller.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { json_response(['status'=>'error','message'=>'Invalid method'],405); }

$payload = json_decode(file_get_contents('php://input'), true) ?? [];
$id   = (int)($payload['brand_id'] ?? 0);
$name = $payload['brand_name'] ?? '';

$res = update_brand_ctr($id, $name);
json_response($res);
