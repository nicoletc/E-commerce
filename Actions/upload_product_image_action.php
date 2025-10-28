<?php
// Actions/upload_product_image_action.php
declare(strict_types=1);

ini_set('display_errors','1');
error_reporting(E_ALL);

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../settings/db_class.php';

header('Content-Type: application/json; charset=utf-8');

// Must be logged in
if (!is_logged_in()) {
  http_response_code(401);
  echo json_encode(['status'=>'error','message'=>'You must be logged in to upload.']);
  exit;
}

$userId    = (int)($_SESSION['customer_id'] ?? 0);
$productId = (int)($_POST['product_id'] ?? 0);

if ($userId <= 0 || $productId <= 0) {
  http_response_code(400);
  echo json_encode(['status'=>'error','message'=>'Invalid user or product id.']);
  exit;
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
  http_response_code(400);
  echo json_encode(['status'=>'error','message'=>'No image uploaded.']);
  exit;
}

// ---- Paths & security: only inside /uploads ----
$uploadsRoot = realpath(__DIR__ . '/../uploads');   // folder already exists (per assignment)
if ($uploadsRoot === false) {
  http_response_code(500);
  echo json_encode(['status'=>'error','message'=>'Uploads folder missing.']);
  exit;
}

$ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
$ok  = ['jpg','jpeg','png','gif','webp'];
if (!in_array($ext, $ok, true)) {
  http_response_code(400);
  echo json_encode(['status'=>'error','message'=>'Unsupported file type.']);
  exit;
}

// Create user/product folders
$uDirAbs = $uploadsRoot . "/u{$userId}";
$pDirAbs = $uDirAbs    . "/p{$productId}";
if (!is_dir($uDirAbs) && !mkdir($uDirAbs, 0777)) {
  http_response_code(500);
  echo json_encode(['status'=>'error','message'=>'Failed to create user folder.']);
  exit;
}
if (!is_dir($pDirAbs) && !mkdir($pDirAbs, 0777)) {
  http_response_code(500);
  echo json_encode(['status'=>'error','message'=>'Failed to create product folder.']);
  exit;
}

// Next index (image_1, image_2…)
$files = glob($pDirAbs.'/image_*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE) ?: [];
$next  = count($files) + 1;

$destAbs = $pDirAbs . "/image_{$next}.{$ext}";

// Final guard: ensure destination stays inside /uploads
$realDest = realpath(dirname($destAbs));
if ($realDest === false || strpos($realDest, $uploadsRoot) !== 0) {
  http_response_code(400);
  echo json_encode(['status'=>'error','message'=>'Invalid destination path.']);
  exit;
}

if (!move_uploaded_file($_FILES['image']['tmp_name'], $destAbs)) {
  http_response_code(500);
  echo json_encode(['status'=>'error','message'=>'Could not store file.']);
  exit;
}

// Store RELATIVE path in DB
$relative = "uploads/u{$userId}/p{$productId}/image_{$next}.{$ext}";

try {
  $pdo = (new Db())->pdo();
  $st  = $pdo->prepare("UPDATE products SET product_image = :img WHERE product_id = :id");
  $st->execute([':img' => $relative, ':id' => $productId]);
  echo json_encode(['status'=>'success','path'=>$relative,'product_id'=>$productId]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['status'=>'error','message'=>'DB update failed.']);
}
