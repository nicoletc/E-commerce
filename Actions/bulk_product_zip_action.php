<?php
// Actions/bulk_product_zip_action.php
declare(strict_types=1);

ini_set('display_errors','1');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/product_controller.php';
require_once __DIR__ . '/../Controllers/category_controller.php';
require_once __DIR__ . '/../Controllers/brand_controller.php';
require_once __DIR__ . '/../Classes/category_class.php';
require_once __DIR__ . '/../Classes/brand_class.php';

function jfail(string $msg, int $code = 400) {
  http_response_code($code);
  echo json_encode(['status'=>'error','message'=>$msg], JSON_UNESCAPED_SLASHES);
  exit;
}
function jsuccess(array $payload) {
  echo json_encode(['status'=>'success'] + $payload, JSON_UNESCAPED_SLASHES);
  exit;
}

if (!function_exists('is_logged_in') || !function_exists('is_admin') || !is_logged_in() || !is_admin()) {
  jfail('Unauthorized. Please log in as admin.', 401);
}

if (!isset($_FILES['zip_file']) || ($_FILES['zip_file']['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
  jfail('No ZIP file received (field name must be "zip_file").');
}
$zipTmp  = (string)$_FILES['zip_file']['tmp_name'];
$zipName = (string)$_FILES['zip_file']['name'];
if (!is_uploaded_file($zipTmp)) jfail('Upload failed or file not found.');



$uploadsRoot = __DIR__ . '/../uploads';
// ensure uploads root exists and is writable
if (!is_dir($uploadsRoot) && !@mkdir($uploadsRoot, 0755, true)) {
  jfail('Could not create uploads/ directory.');
}
// working base is a hidden temp directory inside uploads (allowed by restricted hosts)
$workBase = $uploadsRoot . '/.tmp';
if (!is_dir($workBase) && !@mkdir($workBase, 0775, true)) {
  jfail('Could not create uploads temp directory.');
}
if (!is_writable($workBase)) {
  jfail('Uploads temp directory is not writable.');
}

$workDir = rtrim($workBase, '/').'/bulkupload_'.date('Ymd_His').'_' . bin2hex(random_bytes(4));
if (!@mkdir($workDir, 0775, true)) {
  jfail('Could not create working directory.');
}


// --- extract ZIP ---
$za = new ZipArchive();
if ($za->open($zipTmp) !== true) jfail('Could not open ZIP archive.');
if (!$za->extractTo($workDir)) { $za->close(); jfail('Failed to extract ZIP.'); }
$za->close();

// --- find CSV (first *.csv in root preferred).
// Many ZIPs contain a single top-level folder; look there too.
$csvPath = null;
$extractBase = $workDir;
$rootList = scandir($workDir) ?: [];

// 1) check CSV files at the extraction root
foreach ($rootList as $f) {
  if ($f === '.' || $f === '..') continue;
  $p = $workDir . '/' . $f;
  if (is_file($p) && preg_match('/\.csv$/i', $f)) { $csvPath = $p; break; }
}

// 2) if not found, check each first-level directory for a CSV (common when folder is zipped)
if (!$csvPath) {
  foreach ($rootList as $f) {
    if ($f === '.' || $f === '..') continue;
    $p = $workDir . '/' . $f;
    if (is_dir($p)) {
      $sub = scandir($p) ?: [];
      foreach ($sub as $sf) {
        if ($sf === '.' || $sf === '..') continue;
        $sp = $p . '/' . $sf;
        if (is_file($sp) && preg_match('/\.csv$/i', $sf)) {
          $csvPath = $sp;
          $extractBase = $p; // use this folder as the base for images
          break 2;
        }
      }
    }
  }
}

if (!$csvPath) {
  jfail('No CSV file found in the ZIP. Put the CSV in the root of the archive or inside the top-level folder.');
}

// --- open CSV ---
$fh = fopen($csvPath, 'r');
if (!$fh) jfail('Could not open CSV for reading.');

// --- read headers & index map ---
$headers = fgetcsv($fh);
if (!$headers) { fclose($fh); jfail('CSV is empty (no header row).'); }

// normalize header names (lowercase, trimmed)
// normalize header names (lowercase, trimmed)
$norm = fn($s) => strtolower(trim((string)$s));
$headers = array_map($norm, $headers);

// Accept multiple aliases for cat/brand
$aliases = [
  'product_cat' => ['product_cat','cat','cat_id','category','product_category'],
  'product_brand' => ['product_brand','brand','brand_id','product_brand_id'],
  'product_title' => ['product_title','title','name'],
  'product_price' => ['product_price','price'],
  'product_desc' => ['product_desc','description','product_description','desc'],
  'product_keywords' => ['product_keywords','keywords','product_keywords'],
  'product_image' => ['product_image','image','product_image','image_file']
];

$idx = [];
foreach ($aliases as $key => $alts) {
  $found = false;
  foreach ($alts as $a) {
    $pos = array_search($a, $headers, true);
    if ($pos !== false) { $idx[$key] = $pos; $found = true; break; }
  }
  if (!$found) { fclose($fh); jfail("CSV missing required column (one of): " . implode('|', $alts)); }
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

  // ensure $row has at least up to the highest indexed header
  $maxIndex = max($idx);
  $row = array_pad($row, $maxIndex + 1, '');

  try {
    // skip totally empty rows
    $titleCheck = trim((string)($row[$idx['product_title']] ?? ''));
    $catCheck   = (int)trim((string)($row[$idx['product_cat']] ?? '0'));
    // if both title empty and cat is zero, this is likely an empty row or trailing mapping section -> skip
    if ($titleCheck === '' && $catCheck === 0) {
      continue;
    }

    // Build payload (raw values). Category and brand may be IDs or names.
    $raw_cat   = trim((string)($row[$idx['product_cat']] ?? ''));
    $raw_brand = trim((string)($row[$idx['product_brand']] ?? ''));
    $payload = [
      'product_cat'      => $raw_cat,
      'product_brand'    => $raw_brand,
      'product_title'    => trim((string)($row[$idx['product_title']] ?? '')),
      'product_price'    => trim((string)($row[$idx['product_price']] ?? '')),
      'product_desc'     => trim((string)($row[$idx['product_desc']] ?? '')),
      'product_keywords' => trim((string)($row[$idx['product_keywords']] ?? '')),
      'product_image'    => null,
    ];

    // basic validation
    // Resolve category id: numeric -> use as id; otherwise try find by name or create it
    $catId = 0;
    if ($payload['product_cat'] !== '') {
      if (is_numeric($payload['product_cat']) && (int)$payload['product_cat'] > 0) {
        $catId = (int)$payload['product_cat'];
      } else {
        $catName = trim((string)$payload['product_cat']);
        $catObj = new Category();
        // try to find by exact name
        $list = $catObj->listAll();
        $foundId = 0;
        foreach ($list as $c) { if (strcasecmp($c['cat_name'], $catName) === 0) { $foundId = (int)$c['cat_id']; break; } }
        if ($foundId > 0) {
          $catId = $foundId;
        } else {
          // create new category (admin is running this)
          $nid = $catObj->add($catName);
          if ($nid === null) throw new RuntimeException('Failed to create new category: ' . $catName);
          $catId = (int)$nid;
        }
      }
    }

    $brandId = 0;
    if ($payload['product_brand'] !== '') {
      if (is_numeric($payload['product_brand']) && (int)$payload['product_brand'] > 0) {
        $brandId = (int)$payload['product_brand'];
      } else {
        $brandName = trim((string)$payload['product_brand']);
        $bObj = new Brand();
        $list = $bObj->listAll();
        $foundId = 0;
        foreach ($list as $b) { if (strcasecmp($b['brand_name'], $brandName) === 0) { $foundId = (int)$b['brand_id']; break; } }
        if ($foundId > 0) {
          $brandId = $foundId;
        } else {
          $nid = $bObj->add($brandName);
          if ($nid === null) throw new RuntimeException('Failed to create new brand: ' . $brandName);
          $brandId = (int)$nid;
        }
      }
    }

    if ($catId <= 0) throw new RuntimeException('Invalid product_cat.');
    if ($brandId <= 0) throw new RuntimeException('Invalid product_brand.');
    if ($payload['product_title'] === '') throw new RuntimeException('product_title is required.');
    if ($payload['product_price'] === '' || !is_numeric($payload['product_price'])) {
      throw new RuntimeException('product_price must be numeric.');
    }

  // image resolution (relative to the CSV folder / extract base)
  $imageRel = trim((string)($row[$idx['product_image']] ?? ''));
    $imageAbs = null;
    if ($imageRel !== '') {
      $fname = basename($imageRel);
      // direct candidate
      $candidate = $extractBase . '/' . $fname;
      if (is_file($candidate)) {
        $imageAbs = $candidate;
      } else {
        // try relative path if CSV contained a path (e.g., images/tee1.jpg)
        $relPathCandidate = $extractBase . '/' . ltrim($imageRel, "\/");
        if (is_file($relPathCandidate)) {
          $imageAbs = $relPathCandidate;
        }
      }

      // final fallback: recursive case-insensitive search under extractBase
      if (!$imageAbs) {
        $rit = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($extractBase, FilesystemIterator::SKIP_DOTS));
        foreach ($rit as $file) {
          if ($file->isFile() && strcasecmp($file->getFilename(), $fname) === 0) {
            $imageAbs = $file->getRealPath();
            break;
          }
        }
      }

      if (!$imageAbs) {
        throw new RuntimeException("Image not found near CSV folder: {$fname}");
      }
    }

  // --- create DB product (without image first) ---
  $payloadToDb = $payload;
  $payloadToDb['product_cat'] = $catId;
  $payloadToDb['product_brand'] = $brandId;
  $newId = add_product_ctr($payloadToDb);
    if (!is_int($newId) || $newId <= 0) {
      throw new RuntimeException('DB insert failed or did not return new product id.');
    }

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

      $rel = 'uploads/products/' . (int)$newId . '/' . $safe;
      $ok = update_product_ctr([
        'product_id'       => (int)$newId,
        'product_cat'      => $payload['product_cat'],
        'product_brand'    => $payload['product_brand'],
        'product_title'    => $payload['product_title'],
        'product_price'    => $payload['product_price'],
        'product_desc'     => $payload['product_desc'],
        'product_keywords' => $payload['product_keywords'],
        'product_image'    => $rel,
      ]);
      if (!$ok) {
        throw new RuntimeException('Failed to update product with image path.');
      }
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
