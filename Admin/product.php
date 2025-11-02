<?php
// Admin/product.php
require_once __DIR__ . '/../settings/core.php';
if (!is_logged_in()) { header('Location: ../view/login.php'); exit; }
if (!is_admin())    { header('Location: ../index.php');     exit; }

$customer = $_SESSION['customer_name'] ?? 'Admin';
$first    = htmlspecialchars(explode(' ', trim($customer))[0] ?: 'Admin');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Admin · Products</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link rel="stylesheet" href="../Css/auth_base.css">
  <link rel="stylesheet" href="../Css/admin.css"><!-- reuse the same admin skin -->
</head>
<body>

  <!-- Floating background bubbles (same ambiance as other admin pages) -->
  <div class="bg-bubbles" aria-hidden="true">
    <span class="b b1"></span><span class="b b2"></span><span class="b b3"></span>
    <span class="b b4"></span><span class="b b5"></span><span class="b b6"></span>
  </div>

  <div class="dash">
    <!-- Side bar -->
    <aside class="dash-side">
      <div class="brand">
        <div class="brand-logo">🛍️</div>
        <div class="brand-name">Re.vert Admin</div>
      </div>

      <nav class="side-nav">
        <a class="nav-item" href="category.php">Categories</a>
        <a class="nav-item" href="brand.php">Brands</a>
        <a class="nav-item is-active" href="product.php">Products</a>
        <a class="nav-item" href="#">Customers</a>
        <a class="nav-item" href="#">Reports</a>
        <a class="nav-item" href="#">Settings</a>
      </nav>

      <div class="side-foot">
        <a class="nav-item logout" href="../Actions/logout.php">Logout</a>
      </div>
    </aside>

    <!-- Main -->
    <main class="dash-main">

      <header class="dash-header reveal">
        <div class="hello">
          <h1>Hello, <?= $first ?>! <span class="whatsup">What’s up?</span> 👋</h1>
          <p>Manage your products below.</p>
        </div>

        <div class="header-action">
          <a href="../view/all_product.php" class="btn-storefront" target="_blank" rel="noopener">View Storefront</a>
        </div>
      </header>

      <!-- Hero -->
      <section class="hero reveal" role="img" aria-label="Products highlight">
        <div class="hero-content">
          <div class="chip">New</div>
          <h2>Add products and keep your catalog fresh</h2>
          <p>Attach products to categories & brands, upload an image, and set a price. You can edit later anytime.</p>
        </div>
        <div class="hero-art">
          <span class="ring r1"></span>
          <span class="ring r2"></span>
          <span class="ring r3"></span>
        </div>
      </section>

      <!-- CREATE / UPDATE (one smart form for both) -->
      <article class="card reveal">
        <div class="card-head">
          <h3 id="form-title">Add product</h3>
        </div>

        <form id="product-form" class="row" autocomplete="off" enctype="multipart/form-data">
          <!-- hidden when editing -->
          <input type="hidden" id="product_id" name="product_id" value="">

          <select id="product_cat" name="product_cat" class="input" required title="Category"></select>
          <select id="product_brand" name="product_brand" class="input" required title="Brand"></select>

          <input id="product_title" name="product_title" class="input" type="text" placeholder="Product title" required>
          <input id="product_price" name="product_price" class="input" type="number" min="0" step="0.01" placeholder="Price" required>
          <input id="product_keywords" name="product_keywords" class="input" type="text" placeholder="Keywords (comma separated)">
          <input id="product_image" name="product_image" class="input" type="file" accept="image/*">

          <textarea id="product_desc" name="product_desc" class="input" rows="3" placeholder="Short description"></textarea>

          <button class="btn" type="submit" id="product-submit">Add</button>
          <button class="btn btn--alt" type="button" id="product-cancel" style="display:none;">Cancel edit</button>
        </form>

        <p class="muted">Images are stored safely in <code>/uploads/</code> inside a user and product folder.</p>
      </article>

      <!-- LIST -->
      <article class="card reveal">
        <div class="card-head">
          <h3>Products</h3>
        </div>

        <div class="table-wrap">
          <table class="tbl">
            <thead>
              <tr>
                <th>Item</th>
                <th>Category / Brand</th>
                <th>Price</th>
                <th>Keywords</th>
                <th class="ta-right">Actions</th>
              </tr>
            </thead>
            <tbody id="prod-rows">
              <tr><td colspan="5">Loading…</td></tr>
            </tbody>
          </table>
        </div>
      </article>

      <!-- OPTIONAL: bulk zip upload (extra credit UI) -->
      <article class="card reveal">
        <div class="card-head">
          <h3>Bulk upload (ZIP) — optional</h3>
        </div>
        <form id="bulk-form" class="row" enctype="multipart/form-data">
          <input class="input" type="file" id="bulk_zip" name="bulk_zip" accept=".zip">
          <button class="btn" type="submit">Upload ZIP</button>
          <p class="muted">Include a <code>manifest.csv</code> inside the ZIP with columns:
            <em>product_title,product_price,product_desc,product_keywords,product_cat,product_brand,image</em></p>
        </form>
      </article>

    </main>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../js/product.js"></script>

  <!-- reveal-on-scroll (same behavior as categories/brands) -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const els = document.querySelectorAll('.reveal');
      const io  = new IntersectionObserver((entries) => {
        entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('in'); });
      }, { threshold: 0.08 });
      els.forEach(el => io.observe(el));
    });
  </script>
</body>
</html>
