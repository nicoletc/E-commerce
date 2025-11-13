<?php
// Development-only debug endpoint to inspect cart/session state.
// Remove or restrict in production.
ini_set('display_errors',1); error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../Controllers/cart_controller.php';
require_once __DIR__ . '/../Classes/cart_class.php';

$out = [];
$out['time'] = date('c');
$out['session'] = $_SESSION;
$out['cookies'] = $_COOKIE;

$holder = null;
try { $holder = resolve_cart_holder(null); } catch (Throwable $e) { $holder = ['error' => $e->getMessage()]; }
$out['resolved_holder'] = $holder;

$cid = (int)($_SESSION['customer_id'] ?? 0);
$token = $_SESSION['guest_holder'] ?? ($_COOKIE['cart_token'] ?? null);

$c = new cart_class();
if ($cid > 0) {
  $out['cart_for_customer'] = $c->get_cart_items($cid);
  $out['count_for_customer'] = $c->count_items($cid);
} else {
  $out['cart_for_customer'] = [];
  $out['count_for_customer'] = 0;
}

if (!empty($token)) {
  $out['guest_token'] = $token;
  $out['cart_for_token'] = $c->get_cart_items_by_token($token);
  $out['count_for_token'] = $c->count_items_by_token($token);
} else {
  $out['guest_token'] = null;
  $out['cart_for_token'] = [];
  $out['count_for_token'] = 0;
}

echo json_encode($out, JSON_PRETTY_PRINT);
exit;
