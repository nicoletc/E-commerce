<?php
// Actions/download_bulk_template.php
declare(strict_types=1);
header('Content-Type: application/zip');
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/product_controller.php';
require_once __DIR__ . '/../Controllers/category_controller.php';
require_once __DIR__ . '/../Controllers/brand_controller.php';

if (!function_exists('is_logged_in') || !function_exists('is_admin') || !is_logged_in() || !is_admin()) {
  http_response_code(401);
  echo 'Unauthorized';
  exit;
}

$cols = [
  'product_cat','product_brand','product_title','product_price',
  'product_desc','product_keywords','product_image'
];

$csvLines = [];
$csvLines[] = implode(',', $cols);
// add an example row (blank values)
$csvLines[] = '1,1,Example product,9.99,Short description,"tag1, tag2",example.jpg';

// fetch categories and brands from DB and append mapping sections
$cats = (new product_class())->get_categories();
$brands = (new product_class())->get_brands();

$csvLines[] = '';
$csvLines[] = '';
$csvLines[] = ',,,Category ID Map,,';
$csvLines[] = ',,,cat_id,cat_name';
foreach ($cats as $c) {
  $csvLines[] = ',,,' . (int)$c['cat_id'] . ',"' . str_replace('"', '""', $c['cat_name']) . '"';
}

$csvLines[] = '';
$csvLines[] = ',,,Brand ID Map,,';
$csvLines[] = ',,,brand_id,brand_name';
foreach ($brands as $b) {
  $csvLines[] = ',,,' . (int)$b['brand_id'] . ',"' . str_replace('"', '""', $b['brand_name']) . '"';
}

$readme = "Bulk upload template\n\nInstructions:\n- Keep images inside the ZIP alongside the CSV or in a subfolder (e.g. images/).\n- The CSV must contain a header row with these columns: " . implode(', ', $cols) . ".\n- For product_cat and product_brand you may provide either the numeric id (see maps below) or the exact category/brand name. If a name is provided and does not exist, it will be created.\n- product_image is the filename (or relative path inside the zip) of the image file to copy to the product uploads.\n- Prices must be numeric (e.g. 19.99).\n\nExample row is provided in the CSV.\n";

// create a zip in memory (stream)
$zip = new ZipArchive();
$tmpName = sys_get_temp_dir() . '/bulk_template_' . bin2hex(random_bytes(4)) . '.zip';
if ($zip->open($tmpName, ZipArchive::CREATE) !== true) {
  http_response_code(500);
  echo 'Failed to create ZIP';
  exit;
}
$zip->addFromString('bulkproductsupload.csv', implode("\r\n", $csvLines));
$zip->addFromString('README.txt', $readme);
$zip->close();

header('Content-Disposition: attachment; filename="bulk_template.zip"');
header('Content-Length: ' . filesize($tmpName));
readfile($tmpName);
@unlink($tmpName);
exit;

?>