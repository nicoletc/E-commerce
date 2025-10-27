<?php
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/brand_controller.php';

if (!is_logged_in() || !has_role(ROLE_ADMIN)) { json_response(['status'=>'error','message'=>'Unauthorized'],401); }

$res = fetch_brands_ctr();
json_response($res);
