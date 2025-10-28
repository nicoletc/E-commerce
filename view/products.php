<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/../Controllers/product_controller.php';
require_once __DIR__ . '/../settings/core.php';

$loggedIn = !empty($_SESSION['customer_id']);
$customer = $_SESSION['customer_name'] ?? null;
$isAdmin  = isset($_SESSION['user_role']) && (int)$_SESSION['user_role'] === 1;

$first = $customer ? explode(' ', trim($customer))[0] : null;
$products = get_products_ctr();
$grouped = [];
foreach ($products as $p) {
  $grouped[$p['cat_name']][] = $p;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Re.vert · Products</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link rel="stylesheet" href="../Css/products.css">
</head>
<body>

<div class="bg-bubbles" aria-hidden="true">
  <span class="b b1"></span><span class="b b2"></span><span class="b b3"></span>
  <span class="b b4"></span><span class="b b5"></span><span class="b b6"></span>
</div>

<!-- Header (same as index) -->
<header class="nav">
  <a class="brand" href="../index.php">
    <span class="logo">🛍️</span><span class="name">Re.vert</span>
  </a>
  <nav class="links">
    <a href="../index.php">Home</a>
    <a href="products.php" class="active">Shop Now</a>
    <a href="../index.php#testimonials">Stories</a>
    <a href="../index.php#faq">Help</a>
  </nav>
  <div class="actions">
    <?php if (!$loggedIn): ?>
      <a class="btn ghost" href="login.php">Log in</a>
      <a class="btn" href="register.php">Get Started</a>
    <?php else: ?>
      <?php if ($isAdmin): ?>
        <a class="btn ghost" href="../Admin/category.php">Dashboard</a>
      <?php endif; ?>
      <a class="btn" href="../Actions/logout.php">Logout</a>
    <?php endif; ?>
  </div>
</header>

<!-- Hero -->
<section class="hero container reveal" data-reveal="up" id="top">
  <div class="hero-inner">
    <h1 class="title blur-words reveal" data-reveal="up">Re.vert</h1>
    <p class="sub blur-words delay-150 reveal" data-reveal="up">Your one stop shop for quality products</p>
    <a class="btn glass reveal" data-reveal="up" href="#shop-grid">Shop Now</a>
  </div>
</section>

<!-- Products -->
<section id="shop-grid" class="products-section container reveal" data-reveal="up">
  <?php foreach ($grouped as $category => $items): ?>
    <h2 class="category-title"><?= htmlspecialchars($category) ?></h2>
    <div class="product-grid">
      <?php foreach ($items as $p): ?>
        <article class="product-card" id="product-<?= htmlspecialchars($p['product_id']) ?>">
          <div class="img-wrap">
            <?php if (!empty($p['product_image'])): ?>
              <img src="<?= htmlspecialchars($p['product_image']) ?>" alt="<?= htmlspecialchars($p['product_title']) ?>">
            <?php else: ?>
              <div class="placeholder">No Image</div>
            <?php endif; ?>
          </div>
          <h3><?= htmlspecialchars($p['product_title']) ?></h3>
          <p class="desc"><?= htmlspecialchars($p['product_desc']) ?></p>
          <div class="meta">
            <span class="price">₵<?= htmlspecialchars($p['product_price']) ?></span>
          </div>
          <div class="actions">
            <a class="btn small glass" href="product_detail.php?id=<?= htmlspecialchars($p['product_id']) ?>">View</a>
            <?php if ($loggedIn): ?>
              <a class="btn small ghost" href="#">Add to Cart</a>
            <?php else: ?>
              <a class="btn small ghost disabled" href="login.php" title="Please log in to add to cart">Add to Cart</a>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
</section>

<footer class="footer container">
  <p>© <?= date('Y') ?> Re.vert. All rights reserved.</p>
</footer>

<script src="../js/land_animate.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const els = document.querySelectorAll('.reveal');
    const io  = new IntersectionObserver((entries) => {
      entries.forEach((e) => {
        if (e.isIntersecting) {
          e.target.classList.add('visible');
          io.unobserve(e.target);
        }
      });
    }, { threshold: 0.08 });

    els.forEach(el => io.observe(el));

    // Ensure the hero is visible immediately
    const hero = document.querySelector('.hero');
    if (hero) {
      hero.classList.add('visible');
      // also reveal the blur-words inside the hero
      hero.querySelectorAll('.blur-words').forEach((el, i) => {
        setTimeout(() => el.classList.add('visible'), i * 80);
      });
    }
  });
</script>

</body>
</html>
