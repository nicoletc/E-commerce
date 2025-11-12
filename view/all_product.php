<?php

ini_set('display_errors','1');
ini_set('display_startup_errors','1');
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/../Controllers/product_controller.php';
require_once __DIR__ . '/../settings/core.php';

$loggedIn = !empty($_SESSION['customer_id']);
$customer = $_SESSION['customer_name'] ?? null;
$isAdmin  = isset($_SESSION['user_role']) && (int)$_SESSION['user_role'] === 1;

$first = $customer ? explode(' ', trim($customer))[0] : null;


$cats     = get_categories_ctr();
$brands   = get_brands_ctr();

// collect filters from query string and call controller to get filtered/paginated results
$opts = [
  'q'         => trim((string)($_GET['q'] ?? '')),
  'cat'       => $_GET['cat'] ?? '',
  'brand'     => $_GET['brand'] ?? '',
  'min_price' => $_GET['min_price'] ?? '',
  'max_price' => $_GET['max_price'] ?? '',
  'page'      => max(1, (int)($_GET['page'] ?? 1)),
  'limit'     => 24,
];

$view = view_all_products_ctr($opts);
$products = $view['items'] ?? [];
$total    = $view['total'] ?? 0;
$pages    = $view['pages'] ?? 1;

$grouped = [];
foreach ($products as $p) { $grouped[$p['cat_name']][] = $p; }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Re.vert · All Products</title>
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
    <a href="all_product.php" class="active">Shop Now</a>
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

  <!-- Cart Icon Button -->
<div class="cart-icon-wrapper">
  <a href="cart.php" class="cart-btn" title="View Cart">
    🛒
    <span id="cart-count" class="cart-count-badge">0</span>
  </a>
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

<!-- Filters form moved here: right before the products list -->
<div class="container">
  <div class="filters-card reveal" data-reveal="up" role="region" aria-labelledby="filters-title">
    <h3 id="filters-title" class="filters-title">Search Filters</h3>
    <!-- use GET so filters appear in URL and form can be bookmarked -->
    <form id="search-form" class="filters-form" method="get" action="all_product.php" aria-label="Product filters">
      <div class="filters-row">
        <input id="q" name="q" type="search" placeholder="Search by title or keyword…" autocomplete="off"
               value="<?= htmlspecialchars($opts['q']) ?>">
        <select id="cat" name="cat" aria-label="Category">
          <option value="" <?= $opts['cat'] === '' ? 'selected' : '' ?>>All categories</option>
          <?php foreach ($cats as $c): ?>
            <option value="<?= (int)$c['cat_id'] ?>" <?= ((string)$c['cat_id'] === (string)$opts['cat']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($c['cat_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <select id="brand" name="brand" aria-label="Brand">
          <option value="" <?= $opts['brand'] === '' ? 'selected' : '' ?>>All brands</option>
          <?php foreach ($brands as $b): ?>
            <option value="<?= (int)$b['brand_id'] ?>" <?= ((string)$b['brand_id'] === (string)$opts['brand']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($b['brand_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="filters-row">
        <input id="min_price" name="min_price" type="number" step="0.01" min="0" placeholder="Min price"
               value="<?= htmlspecialchars($opts['min_price']) ?>">
        <input id="max_price" name="max_price" type="number" step="0.01" min="0" placeholder="Max price"
               value="<?= htmlspecialchars($opts['max_price']) ?>">
        <button class="btn primary" type="submit">Search</button>
      </div>
    </form>
  </div>
</div>

<!-- Products -->
<section id="shop-grid" class="products-section container reveal" data-reveal="up">
  <?php foreach ($grouped as $category => $items): ?>
    <h2 class="category-title"><?= htmlspecialchars($category) ?></h2>
    <div class="product-grid">
      <?php foreach ($items as $p): ?>
        <article class="product-card" id="product-<?= htmlspecialchars($p['product_id']) ?>">
          <div class="img-wrap">
            <?php if (!empty($p['product_image'])): ?>
                <img
                  src="<?= htmlspecialchars('../' . ltrim($p['product_image'], '/')) ?>"
                  alt="<?= htmlspecialchars($p['product_title']) ?>"
                >            <?php else: ?>
              <div class="placeholder">No Image</div>
            <?php endif; ?>
          </div>
          <h3><?= htmlspecialchars($p['product_title']) ?></h3>
          <p class="desc"><?= htmlspecialchars($p['product_desc']) ?></p>
          <div class="meta">
            <span class="price">₵<?= htmlspecialchars($p['product_price']) ?></span>
          </div>
          <div class="actions">
            <a class="btn small glass" href="single_product.php?id=<?= htmlspecialchars($p['product_id']) ?>">View</a>
              <a class="btn small ghost" href="#" data-add-pid="<?= (int)$p['product_id'] ?>">Add to Cart</a>
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
<script src="../js/products.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="module">
  import { addToCart, updateCartCount } from '../js/cart.js';

  // Ensure SweetAlert is available, try window.Swal, then dynamic import, then script injection
  async function ensureSwal() {
    if (window.Swal) return window.Swal;
    try {
      const mod = await import('https://cdn.jsdelivr.net/npm/sweetalert2@11');
      if (mod && mod.default) return mod.default;
    } catch (e) {
      // dynamic import failed; try script injection
    }
    return new Promise((resolve) => {
      if (window.Swal) return resolve(window.Swal);
      const s = document.createElement('script');
      s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
      s.onload = () => resolve(window.Swal ?? null);
      s.onerror = () => resolve(null);
      document.head.appendChild(s);
    });
  }

  // Attach per-button click handlers after DOM is ready (more reliable than delegated in some setups)
  document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('[data-add-pid]');
    buttons.forEach(btn => {
      btn.addEventListener('click', async function (e) {
        e.preventDefault();
        const pid = this.getAttribute('data-add-pid');
        if (!pid) return;
        try {
          console.debug('Add-to-cart clicked (button), pid=', pid);
          const res = await addToCart(pid, 1);
          if (res && res.status === 'ok') {
            const Swal = await ensureSwal();
            if (Swal && typeof Swal.fire === 'function') {
              await Swal.fire({ icon: 'success', title: 'Added to cart', timer: 1100, showConfirmButton: false });
            } else {
              console.log('Added to cart');
            }
            if (typeof res.count !== 'undefined') {
              const el = document.getElementById('cart-count'); if (el) el.textContent = String(res.count);
            } else {
              updateCartCount();
            }
          } else if (res && res.status === 'auth') {
            window.location.href = 'login.php?next=' + encodeURIComponent('view/all_product.php');
          } else {
            console.error('Add to cart failed', res);
          }
        } catch (err) { console.error(err); }
      });
    });
  });

  // ensure badge shows up
  document.addEventListener('DOMContentLoaded', () => updateCartCount());
</script>

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

    const hero = document.querySelector('.hero');
    if (hero) {
      hero.classList.add('visible');
      hero.querySelectorAll('.blur-words').forEach((el, i) => {
        setTimeout(() => el.classList.add('visible'), i * 80);
      });
    }
  });
</script>


<!-- bottom duplicate scripts removed; cart functions live in js/cart.js and module above -->


</body>
</html>
