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
// $products = get_products_ctr();
// $cats     = get_categories_ctr();   // <-- add this
// $brands   = get_brands_ctr();       // <-- add this

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
            <?php if ($loggedIn): ?>
              <a class="btn small ghost" href="javascript:void(0)" onclick="addToCart(<?= (int)$p['product_id'] ?>, 1)">Add to Cart</a>
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
<script src="../js/products.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  // exported from PHP so JS knows auth state
  const isLoggedIn = <?= $loggedIn ? 'true' : 'false' ?>;

  // intercept clicks on product view links when not logged in
  document.addEventListener('click', function (e) {
    const a = e.target.closest('a[href*="single_product.php"]');
    if (!a) return;
    if (isLoggedIn) return; // allow navigation for logged-in users

    e.preventDefault();

    // get product id from link
    const href = a.getAttribute('href');
    let id = '';
    try {
      const url = new URL(href, window.location.origin + '<?= dirname($_SERVER['REQUEST_URI']) ?>/');
      id = url.searchParams.get('id') || '';
    } catch (err) {
      // fallback parse
      const m = href.match(/[?&]id=(\d+)/);
      if (m) id = m[1];
    }

    Swal.fire({
      title: 'Please log in',
      text: 'You must be logged in to view product details.',
      icon: 'info',
      showCancelButton: true,
      confirmButtonText: 'Login',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        // redirect to login with next param so user returns to product after logging in
        const next = encodeURIComponent('single_product.php' + (id ? ('?id=' + id) : ''));
        window.location.href = 'login.php?next=' + next;
      }
    });
  }, false);
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


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  // Add-to-cart (global)
  async function addToCart(productId, qty = 1) {
    try {
      const res = await fetch('../Actions/add_to_cart_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: new URLSearchParams({ product_id: productId, qty })
      });
      const data = await res.json();

      if (data.status === 'ok') {
        await Swal.fire({
          icon: 'success',
          title: 'Added to cart',
          text: data.message || 'Item added.',
          timer: 1400,
          showConfirmButton: false
        });
        updateCartCount();
      } else if (data.status === 'auth') {
        Swal.fire({
          icon: 'info',
          title: 'Please log in',
          text: data.message || 'You must be logged in to add items.',
          showCancelButton: true,
          confirmButtonText: 'Login'
        }).then(r => {
          if (r.isConfirmed) {
            window.location.href = 'login.php?next=' + encodeURIComponent('view/all_product.php');
          }
        });
      } else {
        Swal.fire({ icon: 'error', title: 'Could not add', text: data.message || 'Try again.' });
      }
    } catch (e) {
      console.error(e);
      Swal.fire({ icon: 'error', title: 'Network error', text: 'Please try again.' });
    }
  }

  // Cart badge updater
  async function updateCartCount() {
    try {
      const res = await fetch('../Actions/cart_count_action.php', { cache: 'no-store' });
      const data = await res.json();
      const el = document.getElementById('cart-count');
      if (el) el.textContent = (data.count ?? 0);
    } catch (e) {
      console.error('Cart count fetch failed', e);
    }
  }

  document.addEventListener('DOMContentLoaded', updateCartCount);
</script>


<script>

  document.addEventListener('DOMContentLoaded', updateCartCount);
</script>


</body>
</html>
