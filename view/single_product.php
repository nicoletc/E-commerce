<?php

ini_set('display_errors','1');
ini_set('display_startup_errors','1');
error_reporting(E_ALL);


if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../Controllers/product_controller.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$one = $id ? view_single_product_ctr($id) : null;
if (!$one) { http_response_code(404); echo "Product not found"; exit; }

$loggedIn = !empty($_SESSION['customer_id']);
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title><?= h($one['product_title']) ?> · Re.vert</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link rel="stylesheet" href="../Css/products.css">
  <link rel="stylesheet" href="../Css/index.css">
</head>
<body class="detail">

<header class="nav">
  <a class="brand" href="../index.php"><span class="logo">🛍️</span><span class="name">Re.vert</span></a>
  <nav class="links">
    <a href="../index.php">Home</a>
    <a href="all_product.php">All Products</a>
  </nav>
  <div class="actions">
    <?php if (!$loggedIn): ?>
      <a class="btn ghost" href="login.php?next=<?= urlencode('view/single_product.php?id='.$one['product_id']) ?>">Log in</a>
      <a class="btn" href="register.php">Register</a>
    <?php else: ?>
      <a class="btn" href="../Actions/logout.php">Logout</a>
    <?php endif; ?>
  </div>
</header>

<section class="container product-detail">
  <div class="media">
    <?php if (!empty($one['product_image'])): ?>
      <img src="../<?= h($one['product_image']) ?>" alt="<?= h($one['product_title']) ?>">
    <?php else: ?>
      <div class="placeholder">No Image</div>
    <?php endif; ?>
  </div>
  <div class="copy">
    <small class="muted"><?= h($one['cat_name']) ?> / <?= h($one['brand_name']) ?></small>
    <h1><?= h($one['product_title']) ?></h1>
    <div class="price">₵<?= h($one['product_price']) ?></div>
    <p class="desc"><?= h($one['product_desc']) ?></p>
    <?php if (!empty($one['product_keywords'])): ?>
      <div class="keywords"><strong>Keywords:</strong> <?= h($one['product_keywords']) ?></div>
    <?php endif; ?>
    <div class="actions">
      <a class="btn glass" href="javascript:void(0)" onclick="addToCart(<?= (int)$p['product_id'] ?>, 1)">Add to Cart</a>

      <a class="btn ghost" href="all_product.php#product-<?= h($one['product_id']) ?>">Back</a>
    </div>
  </div>
</section>

<script type="module">
  import {addToCart} from '../js/cart.js';
  window.addToCart = addToCart;
</script>

<footer class="footer container">
  <p>© <?= date('Y') ?> Re.vert. All rights reserved.</p>
</footer>
</body>
</html>
