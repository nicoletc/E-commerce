<?php
// Actions/bulk_product_zip_action.php
declare(strict_types=1);

ini_set('display_errors','1');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/product_controller.php';

function jfail(string $msg, int $code = 400) {
  http_response_code($code);
  echo json_encode(['status'=>'error','message'=>$msg], JSON_UNESCAPED_SLASHES);
  exit;
}
function jsuccess(array $payload) {
  echo json_encode(['status'=>'success'] + $payload, JSON_UNESCAPED_SLASHES);
  exit;
}

// --- auth guard (admin only) ---
if (!function_exists('is_logged_in') || !function_exists('is_admin') || !is_logged_in() || !is_admin()) {
  jfail('Unauthorized. Please log in as admin.', 401);
}

// --- upload check ---
if (!isset($_FILES['zip_file']) || ($_FILES['zip_file']['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
  jfail('No ZIP file received (field name must be "zip_file").');
}
$zipTmp  = (string)$_FILES['zip_file']['tmp_name'];
$zipName = (string)$_FILES['zip_file']['name'];
if (!is_uploaded_file($zipTmp)) jfail('Upload failed or file not found.');

// --- working directory (unique) ---
$workBase = __DIR__ . '/../tmp';
if (!is_dir($workBase) && !mkdir($workBase, 0755, true)) {
  jfail('Could not prepare tmp directory.');
}
$workDir = $workBase . '/bulkupload_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4));
if (!mkdir($workDir, 0755, true)) {
  jfail('Could not create working directory.');
}

// --- extract ZIP ---
$za = new ZipArchive();
if ($za->open($zipTmp) !== true) jfail('Could not open ZIP archive.');
if (!$za->extractTo($workDir)) { $za->close(); jfail('Failed to extract ZIP.'); }
$za->close();

// --- find CSV (first *.csv in root preferred) ---
$csvPath = null;
$rootList = scandir($workDir) ?: [];
foreach ($rootList as $f) {
  if ($f === '.' || $f === '..') continue;
  $p = $workDir . '/' . $f;
  if (is_file($p) && preg_match('/\.csv$/i', $f)) { $csvPath = $p; break; }
}
if (!$csvPath) {
  jfail('No CSV file found in the ZIP root. Put the CSV alongside the images.');
}

// --- open CSV ---
$fh = fopen($csvPath, 'r');
if (!$fh) jfail('Could not open CSV for reading.');

// --- read headers & index map ---
$headers = fgetcsv($fh);
if (!$headers) { fclose($fh); jfail('CSV is empty (no header row).'); }

// normalize header names (lowercase, trimmed)
$norm = fn($s) => strtolower(trim((string)$s));
$headers = array_map($norm, $headers);

// expected columns
$need = [
  'product_cat','product_brand','product_title','product_price',
  'product_desc','product_keywords','product_image'
];

$idx = [];
foreach ($need as $col) {
  $pos = array_search($col, $headers, true);
  if ($pos === false) { fclose($fh); jfail("CSV missing required column: {$col}"); }
  $idx[$col] = $pos;
}

// --- uploads root ---
$uploadsRoot = __DIR__ . '/../uploads';
if (!is_dir($uploadsRoot) && !mkdir($uploadsRoot, 0755, true)) {
  fclose($fh);
  jfail('Could not create uploads/ directory.');
}

// --- process rows ---
$created = 0; $skipped = 0; $rows = 0; $errors = [];
while (($row = fgetcsv($fh)) !== false) {
  $rows++;

  // tolerate short rows
  $row += array_fill(0, max($idx), '');

  try {
    $payload = [
      'product_cat'      => (int)trim((string)($row[$idx['product_cat']] ?? '0')),
      'product_brand'    => (int)trim((string)($row[$idx['product_brand']] ?? '0')),
      'product_title'    => trim((string)($row[$idx['product_title']] ?? '')),
      'product_price'    => trim((string)($row[$idx['product_price']] ?? '')),
      'product_desc'     => trim((string)($row[$idx['product_desc']] ?? '')),
      'product_keywords' => trim((string)($row[$idx['product_keywords']] ?? '')),
      'product_image'    => null, // set after file copy
    ];

    // basic validation
    if ($payload['product_cat'] <= 0) throw new RuntimeException('Invalid product_cat.');
    if ($payload['product_brand'] <= 0) throw new RuntimeException('Invalid product_brand.');
    if ($payload['product_title'] === '') throw new RuntimeException('product_title is required.');
    if ($payload['product_price'] === '' || !is_numeric($payload['product_price'])) {
      throw new RuntimeException('product_price must be numeric.');
    }

    // --- resolve image FROM ZIP ROOT (no subfolder) ---
    $imageRel = trim((string)($row[$idx['product_image']] ?? ''));
    $imageAbs = null;
    if ($imageRel !== '') {
      // normalize to filename only (so "images/pic.png" also works)
      $fname = basename($imageRel);

      $candidate = $workDir . '/' . $fname;
      if (is_file($candidate)) {
        $imageAbs = $candidate;
      } else {
        // final attempt: case-insensitive match among files in root
        foreach ($rootList as $f) {
          if ($f === '.' || $f === '..') continue;
          $p = $workDir . '/' . $f;
          if (is_file($p) && strcasecmp($f, $fname) === 0) { $imageAbs = $p; break; }
        }
        if (!$imageAbs) {
          throw new RuntimeException("Image not found in ZIP root: {$fname}");
        }
      }
    }

    // --- create DB product (without image first) ---
    $newId = add_product_ctr($payload);
    if (!$newId) throw new RuntimeException('DB insert failed.');

    // --- place image under uploads/products/{id}/ ---
    if ($imageAbs) {
      $prodDir = $uploadsRoot . '/products/' . (int)$newId;
      if (!is_dir($prodDir) && !mkdir($prodDir, 0755, true)) {
        throw new RuntimeException('Failed to create product uploads directory.');
      }
      $ext = pathinfo($imageAbs, PATHINFO_EXTENSION) ?: 'png';
      $safe = 'main.' . strtolower($ext);
      $destAbs = $prodDir . '/' . $safe;

      if (!copy($imageAbs, $destAbs)) {
        throw new RuntimeException('Failed to copy image to uploads.');
      }

      // store relative path for frontend: "uploads/products/{id}/main.png"
      $rel = 'uploads/products/' . (int)$newId . '/' . $safe;
      update_product_ctr([
        'product_id'       => (int)$newId,
        'product_cat'      => $payload['product_cat'],
        'product_brand'    => $payload['product_brand'],
        'product_title'    => $payload['product_title'],
        'product_price'    => $payload['product_price'],
        'product_desc'     => $payload['product_desc'],
        'product_keywords' => $payload['product_keywords'],
        'product_image'    => $rel,
      ]);
    }

    $created++;
  } catch (Throwable $e) {
    $skipped++;
    $errors[] = "Row {$rows}: " . $e->getMessage();
    // continue to next row
  }
}
fclose($fh);

// --- cleanup tmp dir (best-effort) ---
$it = new RecursiveIteratorIterator(
  new RecursiveDirectoryIterator($workDir, FilesystemIterator::SKIP_DOTS),
  RecursiveIteratorIterator::CHILD_FIRST
);
foreach ($it as $fs) {
  $fs->isDir() ? rmdir($fs->getRealPath()) : @unlink($fs->getRealPath());
}
@rmdir($workDir);

// --- done ---
jsuccess([
  'processed_rows' => $rows,
  'created'        => $created,
  'skipped'        => $skipped,
  'errors'         => $errors,
  'zip_name'       => $zipName,
]);
