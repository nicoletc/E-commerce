<?php
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/product_controller.php';

if (!is_logged_in()) { header('Location: '.app_url('view/login.php?next='.urlencode('view/product_detail.php'))); exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$all = get_products_ctr();
$one = null; foreach ($all as $r){ if ((int)$r['product_id']===$id){ $one=$r; break; } }
if (!$one) { http_response_code(404); echo 'Product not found'; exit; }

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html><head>
  <meta charset="utf-8"><title><?= h($one['product_title']) ?> · E-Shop</title>
  <link rel="stylesheet" href="../Css/auth_base.css">
  <link rel="stylesheet" href="../Css/products.css">
</head><body class="detail">
  <div class="catalog" style="max-width:980px">
    <a class="btn ghost" href="products.php#product-<?= h($one['product_id']) ?>">← Back to products</a>
    <h1><?= h($one['product_title']) ?></h1>
    <p><?= h($one['product_desc']) ?></p>
  </div>
</body></html>
