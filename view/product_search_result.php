<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../Controllers/product_controller.php';
$qInit = trim($_GET['q'] ?? '');

$cats = get_categories_ctr();
$brands = get_brands_ctr();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Search · Re.vert</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link rel="stylesheet" href="../Css/products.css">
  <link rel="stylesheet" href="../Css/index.css">
  <script src="../js/jquery-3.7.1.min.js"></script>
  <script src="../js/storefront.js"></script>
</head>
<body>
<header class="nav">
  <a class="brand" href="../index.php"><span class="logo">🛍️</span><span class="name">Re.vert</span></a>
  <nav class="links">
    <a href="../index.php">Home</a>
    <a href="all_product.php">All Products</a>
    <a class="active" href="#">Search</a>
  </nav>
  <div class="actions">
    <a class="btn ghost" href="login.php">Log in</a>
    <a class="btn" href="register.php">Register</a>
  </div>
</header>

<section class="container">
  <h1 class="page-title">Search</h1>
  <form id="search-form" class="filters">
    <input id="q" type="search" placeholder="Search…" value="<?= htmlspecialchars($qInit) ?>">
    <select id="cat">
      <option value="">All categories</option>
      <?php foreach ($cats as $c): ?>
        <option value="<?= (int)$c['cat_id'] ?>"><?= htmlspecialchars($c['cat_name']) ?></option>
      <?php endforeach; ?>
    </select>
    <select id="brand">
      <option value="">All brands</option>
      <?php foreach ($brands as $b): ?>
        <option value="<?= (int)$b['brand_id'] ?>"><?= htmlspecialchars($b['brand_name']) ?></option>
      <?php endforeach; ?>
    </select>
      <input id="min_price" type="number" step="0.01" min="0" placeholder="Min price">
  <input id="max_price" type="number" step="0.01" min="0" placeholder="Max price">
    <button class="btn">Search</button>
  </form>

  <div id="empty" class="muted" style="display:none; margin-top:16px;">No results.</div>
  <div id="products-grid" class="product-grid"></div>
  <div id="pager" class="pager"></div>
</section>

<script>
  // Fire initial search with q on load
  $(function(){
    $('#search-form').trigger('submit');
  });
</script>
</body>
</html>
