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
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        <a class="btn glass" href="#" data-add-pid="<?= (int)$one['product_id'] ?>">Add to Cart</a>

      <a class="btn ghost" href="all_product.php#product-<?= h($one['product_id']) ?>">Back</a>
    </div>
  </div>
</section>

<script type="module">
  import { addToCart, updateCartCount } from '../js/cart.js';

  // reliable loader for SweetAlert2: prefer window.Swal, then dynamic import, then script injection
  async function ensureSwal() {
    if (window.Swal) return window.Swal;
    try {
      const mod = await import('https://cdn.jsdelivr.net/npm/sweetalert2@11');
      if (mod && mod.default) return mod.default;
    } catch (e) {
      // dynamic import failed, try script injection
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

  // bind the add button(s) on this page after DOM ready
  document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('[data-add-pid]');
    buttons.forEach(btn => btn.addEventListener('click', async function (e) {
      e.preventDefault();
      const pid = this.getAttribute('data-add-pid');
      if (!pid) return;
      try {
        const res = await addToCart(pid, 1);
        console.log('add-to-cart response:', res);
        if (res && res.status === 'ok') {
          if (typeof res.count !== 'undefined') {
            const el = document.getElementById('cart-count'); if (el) el.textContent = String(res.count);
          } else {
            updateCartCount();
          }
          try {
            const Swal = await ensureSwal();
            if (Swal && typeof Swal.fire === 'function') {
              await Swal.fire({ icon: 'success', title: 'Added to cart', timer: 1100, showConfirmButton: false });
            } else {
              alert('Added to cart');
            }
          } catch (err) {
            console.error('Error showing Swal toast', err);
            alert('Added to cart');
          }
        }
      } catch (err) { console.error(err); }
    }));
  });
</script>

<footer class="footer container">
  <p>© <?= date('Y') ?> Re.vert. All rights reserved.</p>
</footer>
</body>
</html>
