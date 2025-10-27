<?php
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/product_controller.php';
require_once __DIR__ . '/upload_helpers.php';

if (!is_logged_in() || !is_admin()) json_response(['status'=>'error','message'=>'Not authorized'], 401);
$user_id = (int)$_SESSION[SESS_USER_ID];

if (empty($_FILES['bulk_zip']['name'])) json_response(['status'=>'error','message'=>'No ZIP uploaded'], 400);

// Save ZIP temporarily inside uploads/tmp (still inside uploads/)
$tmpDir = __DIR__ . '/../uploads/tmp';
if (!is_dir($tmpDir)) @mkdir($tmpDir, 0775, true);

$zipTmp = $tmpDir . '/u' . $user_id . '_' . time() . '.zip';
if (!move_uploaded_file($_FILES['bulk_zip']['tmp_name'], $zipTmp)) {
  json_response(['status'=>'error','message'=>'Cannot save ZIP'], 500);
}

// Unzip
$extractDir = $tmpDir . '/x_' . time();
@mkdir($extractDir, 0775, true);

$zip = new ZipArchive();
if ($zip->open($zipTmp) !== true) json_response(['status'=>'error','message'=>'Invalid ZIP'], 400);
$zip->extractTo($extractDir);
$zip->close();
@unlink($zipTmp);

// Manifest
$manifest = $extractDir . '/manifest.csv';
if (!file_exists($manifest)) {
  json_response(['status'=>'error','message'=>'ZIP must contain manifest.csv'], 400);
}

$fp = fopen($manifest, 'r');
$header = fgetcsv($fp);
$required = ['product_title','product_price','product_desc','product_keywords','product_cat','product_brand','image'];
$map = array_flip($header ?? []);
foreach ($required as $col) if (!isset($map[$col])) json_response(['status'=>'error','message'=>"Missing column: $col"], 400);

// Process
$count = 0; $errors = [];
while (($row = fgetcsv($fp)) !== false) {
  $title = trim($row[$map['product_title']] ?? '');
  $price = trim($row[$map['product_price']] ?? '');
  $desc  = trim($row[$map['product_desc']] ?? '');
  $kw    = trim($row[$map['product_keywords']] ?? '');
  $cat   = (int)($row[$map['product_cat']] ?? 0);
  $brand = (int)($row[$map['product_brand']] ?? 0);
  $img   = trim($row[$map['image']] ?? '');

  if (!$title || !$price || $cat<=0 || $brand<=0) {
    $errors[] = "Skipped row: invalid fields";
    continue;
  }

  $pid = add_product_ctr([
    'user_id' => $user_id,
    'product_cat' => $cat,
    'product_brand' => $brand,
    'product_title' => $title,
    'product_price' => $price,
    'product_desc' => $desc,
    'product_keywords' => $kw,
    'product_image' => null,
  ]);
  if (!$pid) { $errors[] = "Failed to insert product: $title"; continue; }

  // Move image if present
  if ($img && file_exists($extractDir.'/'.$img)) {
    $save = save_uploaded_image_from_path($extractDir.'/'.$img, $user_id, $pid, basename($img));
    if ($save['ok']) {
      update_product_ctr([
        'product_id' => $pid,
        'product_cat' => $cat,
        'product_brand' => $brand,
        'product_title' => $title,
        'product_price' => $price,
        'product_desc' => $desc,
        'product_keywords' => $kw,
        'product_image' => $save['relative']
      ]);
    }
  }
  $count++;
}
fclose($fp);

// cleanup extracted files
function rrmdir($dir){ if (!is_dir($dir)) return; foreach(scandir($dir) as $f){ if($f==='.'||$f==='..') continue; $p="$dir/$f"; if(is_dir($p)) rrmdir($p); else @unlink($p);} @rmdir($dir); }
rrmdir($extractDir);

$msg = "Imported $count item(s)";
if ($errors) $msg .= ' — some rows skipped';
json_response(['status'=>'success','message'=>$msg,'errors'=>$errors]);
